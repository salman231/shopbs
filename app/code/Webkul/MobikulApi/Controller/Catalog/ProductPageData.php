<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MobikulApi
 * @author    Webkul <support@webkul.com>
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html ASL Licence
 * @link      https://store.webkul.com/license.html
 */

namespace Webkul\MobikulApi\Controller\Catalog;

/**
 * Class ProductPageData api
 */
class ProductPageData extends AbstractCatalog
{
    /**
     * Execute Function
     *
     * @return json
     */
    public function execute()
    {
        try {
            $this->verifyRequest();
            $currency = $this->wholeData["currency"] ?? $this->store->getBaseCurrencyCode();
            $cacheString = "PRODUCTPAGEDATA".$this->width.$this->storeId.
            $this->quoteId.($this->productId??$this->sku).$this->customerToken.$currency;
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            // Setting currency /////////////////////////////////////////////////////
            $this->store->setCurrentCurrencyCode($currency);
            $this->product = $this->productFactory->create()->load($this->productId);
            if ($this->sku) {
                $this->product = $this->productRepository->get($this->sku);
                $this->productId = $this->product->getId();
            }
            if (!$this->product->getId()) {
                $this->returnArray["message"] = __("Invalid product.");
                return $this->getJsonResponse($this->returnArray);
            }
            $this->coreRegistry->register("product", $this->product);
            $this->coreRegistry->register("current_product", $this->product);
            $taxHelper = $this->taxHelper;
            $this->isIncludeTaxInPrice = false;
            if ($this->helper->getConfigData("tax/display/type") == 2) {
                $this->isIncludeTaxInPrice = true;
            }
            $this->returnArray["arUrl"] = (string)$this->helperCatalog->getArModelUrl($this->product);
            $this->returnArray["arType"] = $this->helperCatalog->getArModelType($this->product);
            $this->returnArray["arTextureImages"] = $this->helperCatalog->getTextureImages($this->product);
            $ratingArray = [
                "1"=>0,
                "2"=>0,
                "3"=>0,
                "4"=>0,
                "5"=>0
            ];
            $reviews = $this->review
                ->getResourceCollection()
                ->addStoreFilter($this->storeId)
                ->addEntityFilter("product", $this->productId)
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->addRateVotes();
            $this->returnArray["reviewCount"] = $reviews->getSize();
            if ($reviews->getSize() > 0) {
                foreach ($reviews->getItems() as $review) {
                    $oneRating = [];
                    foreach ($review->getRatingVotes() as $vote) {
                        $oneRating[] = $vote->getValue();
                    }
                    $avgRating = array_sum($oneRating)/(count($oneRating) ? count($oneRating) : 1);
                    if ($avgRating < 2) {
                        $avgRating = 1;
                    } elseif ($avgRating < 3) {
                        $avgRating = 2;
                    } elseif ($avgRating < 4) {
                        $avgRating = 3;
                    } elseif ($avgRating < 5) {
                        $avgRating = 4;
                    } elseif ($avgRating == 5) {
                        $avgRating = 5;
                    }
                    $ratingArray[$avgRating] = ($ratingArray[$avgRating] ?? 0)+1;
                }
            }
            $this->returnArray["ratingArray"] = $ratingArray;
            $this->getProductBasicDeatils();
            // Getting price format /////////////////////////////////////////////////
            $this->returnArray["priceFormat"] = $this->localeFormat->getPriceFormat();
            // Getting image galleries //////////////////////////////////////////////
            $this->getProductImages();
            // Getting additional information ///////////////////////////////////////
            $this->getAdditionalInformation();
            // Getting rating form data /////////////////////////////////////////////
            $this->getRatingAndReviewData();
            // Getting custom options ///////////////////////////////////////////////
            $this->getCustomOptions();
            // Getting downloadable product data ////////////////////////////////////
            $this->getDownloadableData();
            // Getting grouped product data /////////////////////////////////////////
            $this->getGroupedProductData();
            // Getting bundle product options ///////////////////////////////////////
            $this->getBundleProductData();
            // Configurable product options /////////////////////////////////////////
            if ($this->product->getTypeId() == "configurable") {
                $configurableBlock = $this->configurableBlock;
                $this->returnArray["configurableData"] = $configurableBlock->getJsonConfig1();
            }
            if ($this->product->getTypeId() == "Membership") {
                $membershipBlock = $this->membershipBlock;
                $this->returnArray["membershipData"] = $membershipBlock->getMembershipOptions($this->product->getId());
                $this->getDiscountableProducts($this->productId);
            }
            //checking if the product is new/////////////////////////////////////////
            $this->returnArray["is_new"] = $this->isProductNew();
            // Getting tier prices //////////////////////////////////////////////////
            $this->getTierPrice();
            // Getting related product list /////////////////////////////////////////
            $this->getRelatedProduct();
            // Getting upsell product list //////////////////////////////////////////
            $this->getUpsellProduct();
            $quote = new \Magento\Framework\DataObject();
            if ($this->customerId != 0) {
                $quote = $customerQuote = $this->helper->getCustomerQuote($this->customerId);
                $this->returnArray["cartCount"] = $this->helper->getCartCount($customerQuote);
                // Checking for product in wishlist /////////////////////////////////
                $wishlist = $this->wishlist->create()->loadByCustomerId($this->customerId, true);
                $wishListCollection = $wishlist->getItemCollection()->addFieldToFilter(
                    "main_table.product_id",
                    $this->productId
                );
                $item = $wishListCollection->getFirstItem();
                $this->returnArray["isInWishlist"] = !!$item->getId();
                if ($this->returnArray["isInWishlist"]) {
                    $this->returnArray["wishlistItemId"] = $item->getId();
                }
            }
            if ($this->quoteId != 0) {
                $quote = $this->quoteModel->setStoreId($this->storeId)->load($this->quoteId);
                $this->returnArray["cartCount"] = $this->helper->getCartCount($quote);
            }
            // check for downloadable product guest checkout ////////////////////////
            $this->returnArray["canGuestCheckoutDownloadable"] = false;
            if ($this->helper->getConfigData("catalog/downloadable/disable_guest_checkout") == 0) {
                $this->returnArray["canGuestCheckoutDownloadable"] = true;
            }
            $stockItem = $this->stockRegistry->getStockItem(
                $this->product->getId(),
                $this->product->getStore()->getWebsiteId()
            );
            $minQty = $stockItem->getMinSaleQty();
            $config = $this->product->getPreconfiguredValues();
            $configQty = $config->getQty();
            if ($configQty > $minQty) {
                $minQty = $configQty;
            }
            if ($quote instanceof \Magento\Quote\Model\Quote) {
                $this->returnArray["isCheckoutAllowed"] = $this->checkoutHelper->isAllowedGuestCheckout($quote);
            } else {
                $this->returnArray["isCheckoutAllowed"] = true;
            }
            $this->returnArray["minAddToCartQty"] = $minQty;
            $this->returnArray["success"] = true;
            $this->customerSession->setCustomerId(null);
            $this->emulate->stopEnvironmentEmulation($environment);
            $this->checkNGenerateEtag($cacheString);
            return $this->getJsonResponse($this->returnArray);
        } catch (\Exception $e) {
            $this->returnArray["message"] = __($e->getMessage());
            $this->helper->printLog($this->returnArray);
            return $this->getJsonResponse($this->returnArray);
        }
    }

    /**
     * Function verify Request to authenticate the request
     * Authenticates the request and logs the result for invalid requests
     *
     * @return Json
     */
    protected function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "GET" && $this->wholeData) {
            $this->eTag = $this->wholeData["eTag"] ?? "";
            $this->width = $this->wholeData["width"] ?? 1000;
            $this->storeId = $this->wholeData["storeId"] ?? 0;
            $this->quoteId = $this->wholeData["quoteId"] ?? 0;
            $this->productId = $this->wholeData["productId"] ?? 0;
            $this->sku = $this->wholeData["sku"] ?? '';
            $this->customerToken = $this->wholeData["customerToken"] ?? "";
            $this->customerId = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            // Checking customer token //////////////////////////////////////////////
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["message"] = __(
                    "Customer you are requesting does not exist, so you need to logout."
                );
                $this->returnArray["otherError"] = "customerNotExist";
                $this->customerId = 0;
            } elseif ($this->customerId != 0) {
                $this->customerSession->setCustomerId($this->customerId);
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    /**
     * Function isProductNew
     *
     * @return bool
     */
    protected function isProductNew()
    {
        $todayStartOfDayDate = $this->localeDate->date()->setTime(0, 0, 0)->format("Y-m-d H:i:s");
        $todayEndOfDayDate = $this->localeDate->date()->setTime(23, 59, 59)->format("Y-m-d H:i:s");
        $productNewFromDate = $this->product->getNewsFromDate();
        $productNewToDate = $this->product->getNewsToDate();
        if (strtotime($todayStartOfDayDate) > strtotime($productNewFromDate)
            && strtotime($todayEndOfDayDate) < strtotime($productNewToDate)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function getSelectionTitlePrice
     *
     * @param float  $amount           amount
     * @param object $selection        selection
     * @param bool   $includeContainer includeContainer
     *
     * @return string
     */
    protected function getSelectionTitlePrice($amount, $selection, $includeContainer = true)
    {
        $priceTitle = '<span class="product-name">' .
            $this->helperCatalog->escapeHtml($selection->getName()) . '</span>';
        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+' .
            $this->getPriceInCurrency($amount). ($includeContainer ? '</span>' : '');
        return $priceTitle;
    }

    /**
     * Function getSelectionQtyTitlePrice
     *
     * @param float  $amount           amount
     * @param object $selection        selection
     * @param bool   $includeContainer includeContainer
     *
     * @return float
     */
    protected function getSelectionQtyTitlePrice($amount, $selection, $includeContainer = true)
    {
        $priceTitle = '<span class="product-name">' . $selection->getSelectionQty() * 1 . ' x ' .
            $this->helperCatalog->escapeHtml($selection->getName()) . '</span>';
        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '').'+'.
            $this->getPriceInCurrency($amount).($includeContainer ? '</span>' : '');
        return $priceTitle;
    }

    /**
     * Function getSavePercent
     *
     * @param float $productPriceAmount productPriceAmount
     * @param float $amount             amount
     *
     * @return float
     */
    protected function getSavePercent($productPriceAmount, $amount)
    {
        return round(100 - ((100 / $productPriceAmount) * $amount));
    }

    /**
     * Function checkIsInRange
     *
     * @return bool
     */
    protected function checkIsInRange()
    {
        $fromdate = $this->product->getSpecialFromDate();
        $todate = $this->product->getSpecialToDate();
        $isInRange = false;
        if (isset($fromdate) && isset($todate)) {
            $today = $this->date->date("Y-m-d H:i:s");
            $todayTime = strtotime($today);
            $fromTime = strtotime($fromdate);
            $toTime = strtotime($todate);
            if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                $isInRange = true;
            }
        }
        if (isset($fromdate) && !isset($todate)) {
            $today = $this->date->date("Y-m-d H:i:s");
            $todayTime = strtotime($today);
            $fromTime = strtotime($fromdate);
            if ($todayTime >= $fromTime) {
                $isInRange = true;
            }
        }
        if (!isset($fromdate) && isset($todate)) {
            $today = $this->date->date("Y-m-d H:i:s");
            $today_time = strtotime($today);
            $from_time = strtotime($fromdate);
            if ($today_time <= $from_time) {
                $isInRange = true;
            }
        }
        $this->returnArray["isInRange"] = $isInRange;
    }

    /**
     * Function getProductImages
     *
     * @return void
     */
    protected function getProductImages()
    {
        $imageGallery = [];
        $galleryCollection = $this->product->getMediaGalleryImages();
        foreach ($galleryCollection as $image) {
            $eachImage = [];
            $eachImage["isVideo"] = false;
            $eachImage["videoUrl"] = "";
            $eachImage["smallImage"] = $this->imageHelper->init($this->product, "product_page_image_small")
                ->keepFrame(false)->resize($this->width/3)->setImageFile($image->getFile())->getUrl();
            $eachImage["largeImage"] = $this->imageHelper->init($this->product, "product_page_image_large")
                ->keepFrame(false)->resize($this->width)->setImageFile($image->getFile())->getUrl();
            $eachImage["dominantColor"] = $this->helper->getDominantColor($image->getPath());
            if ($image->getMediaType() == "external-video") {
                $eachImage["isVideo"] = true;
                $eachImage["videoUrl"] = $image->getVideoUrl();
            }
            if ($this->product->getImage() == $image->getFile()) {
                array_unshift($imageGallery, $eachImage);
            } else {
                $imageGallery[] = $eachImage;
            }
        }
        if (empty($imageGallery)) {
            $imageGallery[0]["smallImage"] = $this->imageHelper->getDefaultPlaceholderUrl("thumbnail");
            $imageGallery[0]["largeImage"] = $this->imageHelper->getDefaultPlaceholderUrl("image");
            $imageGallery[0]["dominantColor"] = $this->helper->getDominantColor(
                $this->helper->getDominantColorFilePath($imageGallery[0]["smallImage"])
            );
        }
        $this->returnArray["imageGallery"] = $imageGallery;
        $this->returnArray["thumbNail"] = $this->helperCatalog->getImageUrl($this->product, $this->width/2.5);
        $this->returnArray["dominantColor"] = $this->helper->getDominantColor(
            $this->helper->getDominantColorFilePath($this->returnArray["thumbNail"])
        );
    }

    /**
     * Function getAdditionalInformation
     *
     * @return void
     */
    protected function getAdditionalInformation()
    {
        $additionalInformation = [];
        foreach ($this->product->getAttributes() as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), [])) {
                $value = $attribute->getFrontend()->getValue($this->product);
                if (!$this->product->hasData($attribute->getAttributeCode())) {
                    $value = __("N/A");
                } elseif ((string)$value == "") {
                    $value = __("No");
                } elseif ($attribute->getFrontendInput() == "price" && is_string($value)) {
                    $value = $this->helperCatalog->stripTags($this->getPriceInCurrency($value));
                }
                if (is_string($value) && strlen($value)) {
                    $eachAttribute = [];
                    $eachAttribute["label"] = htmlspecialchars_decode($attribute->getStoreLabel());
                    $eachAttribute["value"] = htmlspecialchars_decode($value);
                    $additionalInformation[] = $eachAttribute;
                }
            }
        }
        $this->returnArray["additionalInformation"] = $additionalInformation;
    }

    /**
     * Function getRatingAndReviewData
     *
     * @return void
     */
    protected function getRatingAndReviewData()
    {
        $ratingFormData = [];
        $ratingCollection = $this->rating
            ->getResourceCollection()
            ->addEntityFilter("product")
            ->setPositionOrder()
            ->setStoreFilter($this->storeId)
            ->addRatingPerStoreName($this->storeId)
            ->load()
            ->addOptionToItems();
        foreach ($ratingCollection as $rating) {
            $eachTypeRating = [];
            $eachRatingFormData = [];
            foreach ($rating->getOptions() as $option) {
                $eachTypeRating[] = $option->getId();
            }
            $eachRatingFormData["id"] = $rating->getId();
            $eachRatingFormData["name"] = $this->helperCatalog->stripTags($rating->getRatingCode());
            $eachRatingFormData["values"] = $eachTypeRating;
            $ratingFormData[] = $eachRatingFormData;
        }
        $this->returnArray["ratingFormData"] = $ratingFormData;
        // Getting rating data //////////////////////////////////////////////////////
        $ratingCollection->addEntitySummaryToItem($this->productId, $this->storeId);
        $ratingData = [];
        foreach ($ratingCollection as $rating) {
            if ($rating->getSummary()) {
                $eachRating = [];
                $eachRating["ratingCode"] = $this->helperCatalog->stripTags($rating->getRatingCode());
                $eachRating["ratingValue"] = number_format((5 * $rating->getSummary()) / 100, 1, ".", "");
                $ratingData[] = $eachRating;
            }
        }
        $this->returnArray["ratingData"] = $ratingData;
        // Getting review list //////////////////////////////////////////////////////
        $reviewList = [];
        $ratingsArr = [];
        $reviewCollection = $this->review
            ->getResourceCollection()
            ->addStoreFilter($this->storeId)
            ->addEntityFilter("product", $this->productId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setPageSize(5)
            ->setCurPage(1)
            ->setDateOrder()
            ->addRateVotes();
        foreach ($reviewCollection as $review) {
            $oneReview = [];
            $ratings = [];
            $oneReview["title"] = $this->helperCatalog->stripTags($review->getTitle());
            $oneReview["details"] = $this->helperCatalog->stripTags($review->getDetail());
            $votes = $review->getRatingVotes();
            $totalRatings = 0;
            $totalRatingsCount = 0;
            if (count($votes)) {
                foreach ($votes as $_vote) {
                    $oneVote = [];
                    $oneVote["label"] = $this->helperCatalog->stripTags($_vote->getRatingCode());
                    $oneVote["value"] = number_format($_vote->getValue(), 1, ".", "");
                    $totalRatings += number_format($_vote->getValue(), 1, ".", "");
                    $totalRatingsCount++;
                    $ratings[] = $oneVote;
                    $ratingsArr[] = $_vote->getPercent();
                }
            }
            $oneReview["avgRatings"] = number_format(
                $totalRatings/($totalRatingsCount ? $totalRatingsCount: 1),
                1,
                ".",
                ""
            );
            $oneReview["ratings"] = $ratings;
            $oneReview["reviewBy"] = __("Review by %1", $this->helperCatalog->stripTags($review->getNickname()));
            $oneReview["reviewOn"] = __(
                "(Posted on %1)",
                $this->helperCatalog->formatDate($review->getCreatedAt()),
                "long"
            );
            $reviewList[] = $oneReview;
        }
        $this->returnArray["reviewList"] = $reviewList;
        $ratingVal = 0;
        if (count($ratingsArr) > 0) {
            $ratingVal = number_format((5 * (array_sum($ratingsArr) / count($ratingsArr))) / 100, 1, ".", "");
        }
        $this->returnArray["rating"] = $ratingVal;
    }

    /**
     * Function getCustomOptions
     *
     * @return void
     */
    protected function getCustomOptions()
    {
        $optionBlock = $this->productOptionBlock;
        $options = $optionBlock->decorateArray($optionBlock->getOptions());
        $customOptions = [];
        if (count($options)) {
            $eachOption = [];
            foreach ($options as $option) {
                $eachOption = $option->getData();
                $eachOption["unformatted_default_price"] = $option->getDefaultPrice();
                $eachOption["formatted_default_price"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($option->getDefaultPrice())
                );
                $eachOption["unformatted_price"] = $option->getPrice();
                $eachOption["formatted_price"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($option->getPrice())
                );
                $optionValueCollection = $option->getValues();
                if (is_array($optionValueCollection) || is_object($optionValueCollection)) {
                    foreach ($optionValueCollection as $optionValue) {
                        $eachOptionValue = [];
                        $eachOptionValue = $optionValue->getData();
                        $eachOptionValue["formatted_price"] = $this->helperCatalog->stripTags(
                            $this->getPriceInCurrency($optionValue->getPrice())
                        );
                        $eachOptionValue["formatted_default_price"] = $this->helperCatalog->stripTags(
                            $this->getPriceInCurrency($optionValue->getDefaultPrice())
                        );
                        $eachOption["optionValues"][] = $eachOptionValue;
                    }
                }
                $customOptions[] = $eachOption;
            }
            $this->returnArray["customOptions"] = $customOptions;
        }
    }

    /**
     * Function getDownloadableData
     *
     * @return void
     */
    protected function getDownloadableData()
    {
        if ($this->product->getTypeId() == "downloadable") {
            $linkArray = [];
            $downloadableBlock = $this->productLinks;
            $linkArray["title"] = $downloadableBlock->getLinksTitle();
            $linkArray["linksPurchasedSeparately"] = $downloadableBlock->getLinksPurchasedSeparately();
            $links = $downloadableBlock->getLinks();
            $linkData = [];
            foreach ($links as $link) {
                $eachLink = [];
                $eachLink["id"] = $linkId = $link->getId();
                $eachLink["price"] = $link->getPrice();
                // $this->getPriceInCurrency($link->getPrice(), true, false);
                $eachLink["linkTitle"] = $link->getTitle() ? htmlspecialchars_decode(
                    $link->getTitle(),
                    ENT_QUOTES
                ) : "";
                $eachLink["formattedPrice"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($link->getPrice())
                );
                if ($link->getSampleFile() || $link->getSampleUrl()) {
                    $link = $this->linkFactory->create()->load($linkId);
                    if ($link->getId()) {
                        if ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                            $eachLink["url"] = $link->getSampleUrl();
                            $buffer = $this->fileDriver->fileGetContents($link->getSampleUrl());
                            $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
                            $eachLink["mimeType"] = $fileInfo->buffer($buffer);
                            $fileArray = explode(DS, $link->getSampleUrl());
                            $eachLink["fileName"] = end($fileArray);
                        } elseif ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                            $baseSamplePath = $this->linkFactory->create()->getBaseSamplePath();
                            $sampleLinkFilePath = $this->downloadHelper->getFilePath(
                                $baseSamplePath,
                                $link->getSampleFile()
                            );
                            $eachLink["url"] = $this->storeInterface->getStore()
                                ->getUrl("mobikulhttp/download/downloadlinksample", ["linkId"=>$linkId]);
                            $fileArray = explode(DS, $sampleLinkFilePath);
                            $eachLink["mimeType"] = mime_content_type($this->baseDir.DS.$sampleLinkFilePath);
                            $eachLink["fileName"] = end($fileArray);
                        }
                    }
                    $eachLink["haveLinkSample"] = 1;
                    $eachLink["linkSampleTitle"] = __("sample");
                }
                $linkData[] = $eachLink;
            }
            $linkArray["linkData"] = $linkData;
            $this->returnArray["links"] = $linkArray;
            $linkSampleArray = [];
            $downloadableSampleBlock = $this->productSample;
            $linkSampleArray["hasSample"] = $downloadableSampleBlock->hasSamples();
            $linkSampleArray["title"] = htmlspecialchars_decode($downloadableSampleBlock->getSamplesTitle());
            $linkSamples = $downloadableSampleBlock->getSamples();
            $linkSampleData = [];
            foreach ($linkSamples as $linkSample) {
                $eachSample = [];
                $sampleId = $linkSample->getId();
                $eachSample["sampleTitle"] = htmlspecialchars_decode(
                    $this->helperCatalog->stripTags($linkSample->getTitle())
                );
                $sample = $this->downloadSample->load($sampleId);
                if ($sample->getId()) {
                    if ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                        $eachSample["url"] = $sample->getSampleUrl();
                        $buffer = $this->fileDriver->fileGetContents($eachSample["url"]);
                        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
                        $eachSample["mimeType"] = $fileInfo->buffer($buffer);
                        $fileArray = explode(DS, $sample->getSampleUrl());
                        $eachSample["fileName"] = end($fileArray);
                    } elseif ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                        $sampleFilePath = $this->downloadHelper->getFilePath(
                            $sample->getBasePath(),
                            $sample->getSampleFile()
                        );
                        $eachSample["url"] = $this->configurableBlock->getUrl(
                            "downloadable/download/sample",
                            ["sample_id"=>$sampleId]
                        );
                        $fileArray = explode(DS, $sampleFilePath);
                        $eachSample["mimeType"] = mime_content_type($this->baseDir.DS.$sampleFilePath);
                        $eachSample["fileName"] = end($fileArray);
                    }
                }
                $linkSampleData[] = $eachSample;
            }
            $linkSampleArray["linkSampleData"] = $linkSampleData;
            $this->returnArray["samples"] = $linkSampleArray;
        }
    }

    /**
     * Function getGroupedProductData
     *
     * @return void
     */
    protected function getGroupedProductData()
    {
        if ($this->product->getTypeId() == "grouped") {
            $groupedParentId = $this->groupedProduct->getParentIdsByChild($this->product->getId());
            $associatedProducts = $this->product->getTypeInstance(true)->getAssociatedProducts($this->product);
            $minPrice = [];
            $groupedData = [];
            foreach ($associatedProducts as $associatedProduct) {
                $defaultQty = $associatedProduct->getQty();
                $associatedProduct = $this->productFactory->create()->load($associatedProduct->getId());
                $eachAssociatedProduct = [];
                $eachAssociatedProduct["name"] = $this->helperCatalog->stripTags($associatedProduct->getName());
                $eachAssociatedProduct["id"] = $associatedProduct->getId();
                if ($associatedProduct->isAvailable()) {
                    $eachAssociatedProduct["isAvailable"] = (bool)$associatedProduct->isAvailable();
                } else {
                    $eachAssociatedProduct["isAvailable"] = false;
                }
                $fromdate = $associatedProduct->getSpecialFromDate();
                $todate = $associatedProduct->getSpecialToDate();
                $isInRange = false;
                if (isset($fromdate) && isset($todate)) {
                    $today = $this->date->date("Y-m-d H:i:s");
                    $todayTime = $this->date->timestamp($today);
                    $fromTime = $this->date->timestamp($fromdate);
                    $toTime = $this->date->timestamp($todate);
                    if ($todayTime >= $fromTime && $todayTime <= $toTime) {
                        $isInRange = true;
                    }
                }
                if (isset($fromdate) && !isset($todate)) {
                    $today = $this->date->date("Y-m-d H:i:s");
                    $todayTime = $this->date->timestamp($today);
                    $fromTime = $this->date->timestamp($fromdate);
                    if ($todayTime >= $fromTime) {
                        $isInRange = true;
                    }
                }
                if (!isset($fromdate) && isset($todate)) {
                    $today = $this->date->date("Y-m-d H:i:s");
                    $todayTime = $this->date->timestamp($today);
                    $fromTime = $this->date->timestamp($fromdate);
                    if ($todayTime <= $fromTime) {
                        $isInRange = true;
                    }
                }
                $eachAssociatedProduct["isInRange"] = $isInRange;
                $eachAssociatedProduct["defaultQty"] = (int)$defaultQty;
                $eachAssociatedProduct["specialPrice"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($associatedProduct->getSpecialPrice())
                );
                $eachAssociatedProduct["foramtedPrice"] = $this->helperCatalog->stripTags(
                    $this->getPriceInCurrency($associatedProduct->getPrice())
                );
                $eachAssociatedProduct["thumbNail"] = $this->helperCatalog->getImageUrl(
                    $associatedProduct,
                    $this->width/5
                );
                $eachAssociatedProduct["dominantColor"] = $this->helper->getDominantColor(
                    $this->helper->getDominantColorFilePath(
                        $this->helperCatalog->getImageUrl($associatedProduct, $this->width/5)
                    )
                );
                $groupedData[] = $eachAssociatedProduct;
            }
            $this->returnArray["groupedData"] = $groupedData;
            $minPrice = 0;
            if ($this->product->getMinimalPrice() == "") {
                $associatedProducts = $this->product->getTypeInstance(true)->getAssociatedProducts($this->product);
                $minPriceArr = [];
                foreach ($associatedProducts as $associatedProduct) {
                    if ($ogPrice = $associatedProduct->getPrice()) {
                        $minPriceArr[] = $ogPrice;
                    }
                }
                if (!empty($minPriceArr)) {
                    $minPrice = min($minPriceArr);
                }
            } else {
                $minPrice = $this->product->getMinimalPrice();
            }
            if ($this->isIncludeTaxInPrice) {
                $this->returnArray["groupedPrice"] = $this->helperCatalog->stripTags(
                    $this->pricingHelper->currency($this->taxHelper->getTaxPrice($this->product, $minPrice))
                );
            } else {
                $this->returnArray["groupedPrice"] = $this->helperCatalog->stripTags(
                    $this->pricingHelper->currency($minPrice)
                );
            }
        }
    }

    /**
     * Function getBundleProductData
     *
     * @return void
     */
    protected function getBundleProductData()
    {
        if ($this->product->getTypeId() == "bundle") {
            $typeInstance = $this->product->getTypeInstance(true);
            $typeInstance->setStoreFilter($this->product->getStoreId(), $this->product);
            $optionCollection = $typeInstance->getOptionsCollection($this->product);
            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($this->product),
                $this->product
            );
            $bundleOptionCollection = $optionCollection
                ->appendSelections(
                    $selectionCollection,
                    false,
                    $this->productFactory->create()->getSkipSaleableCheck()
                );
            $bundleOptions = [];
            foreach ($bundleOptionCollection as $bundleOption) {
                $oneOption = [];
                if (!$bundleOption->getSelections()) {
                    continue;
                }
                $oneOption = $bundleOption->getData();
                $selections = $bundleOption->getSelections();
                unset($oneOption["selections"]);
                $bundleOptionValues = [];
                foreach ($selections as $selection) {
                    $eachBundleOptionValues = [];
                    if ($selection->isSaleable()) {
                        $coreHelper = $this->pricingHelper;
                        $price = $this->product->getPriceModel()->getSelectionPreFinalPrice(
                            $this->product,
                            $selection,
                            1
                        );
                        $priceTax = $this->taxHelper->getTaxPrice($this->product, $price);
                        if ($oneOption["type"] == "checkbox" || $oneOption["type"] == "multi") {
                            $eachBundleOptionValues["title"] = str_replace(
                                "&nbsp;",
                                " ",
                                $this->helperCatalog->stripTags(
                                    $this->getSelectionQtyTitlePrice($priceTax, $selection, true)
                                )
                            );
                        }
                        if ($oneOption["type"] == "radio" || $oneOption["type"] == "select") {
                            $eachBundleOptionValues["title"] = str_replace(
                                "&nbsp;",
                                " ",
                                $this->helperCatalog->stripTags(
                                    $this->getSelectionTitlePrice($priceTax, $selection, false)
                                )
                            );
                        }
                        $eachBundleOptionValues["price"] = $coreHelper->currencyByStore(
                            $priceTax,
                            $this->product->getStore(),
                            false,
                            false
                        );
                        $eachBundleOptionValues["isSingle"] = (count($selections) == 1 && $bundleOption->getRequired());
                        $eachBundleOptionValues["isDefault"] = $selection->getIsDefault();
                        $eachBundleOptionValues["defaultQty"] = $selection->getSelectionQty();
                        $eachBundleOptionValues["optionValueId"] = $selection->getSelectionId();
                        $eachBundleOptionValues["foramtedPrice"] = $coreHelper->currencyByStore(
                            $priceTax,
                            $this->product->getStore(),
                            true,
                            true
                        );
                        $eachBundleOptionValues["isQtyUserDefined"] = $selection->getSelectionCanChangeQty();
                        $bundleOptionValues[] = $eachBundleOptionValues;
                    }
                }
                $oneOption["optionValues"] = $bundleOptionValues;
                $bundleOptions[] = $oneOption;
            }
            $this->returnArray["bundleOptions"] = $bundleOptions;
            $this->returnArray["priceView"] = $this->product->getPriceView();
        }
    }

    /**
     * Function getTierPrice
     *
     * @return void
     */
    protected function getTierPrice()
    {
        $groupId = 0;
        if ($this->customerId) {
            $groupId = $this->customerFactory->create()->load($this->customerId)->getGroupId();
        }
        $allTierPrices = [];
        $tierPrices = $this->product->getData('tier_price');
        if ($tierPrices && count($tierPrices) > 0) {
            foreach ($tierPrices as $index => $price) {
                if ($price["cust_group"] == 32000 || $price["cust_group"] == $groupId) {
                    $allTierPrices[] = __(
                        'Buy %1 for %2 each and <strong class="benefit">save<span class="percent tier-%3">
                        &nbsp;%4</span>%</strong>',
                        round($price["price_qty"]),
                        $this->getPriceInCurrency($this->helperCatalog->stripTags($price["price"])),
                        $index,
                        $this->getSavePercent($this->product->getFinalPrice(), $price["price"])
                    );
                }
            }
            $this->returnArray["tierPrices"] = $allTierPrices;
        }
    }

    /**
     * Function getRelatedProduct
     *
     * @return void
     */
    protected function getRelatedProduct()
    {
        $relatedProductCollection = $this->product->getRelatedProductIds();
        $relatedProductList = [];
        if (count($relatedProductCollection) > 0) {
            $productCollection = $this->productFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addFieldToFilter("entity_id", ["in"=>$relatedProductCollection])
                ->setPageSize(5)
                ->setCurPage(1);
            foreach ($productCollection as $eachProduct) {
                if ($eachProduct->isAvailable()) {
                    $relatedProductList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
            }
        }
        $this->returnArray["relatedProductList"] = $relatedProductList;
    }
    public function getDiscountableProducts($productid){
        $memberproductId = $productid;
        
        $model = $this->_MembershipProductsFactory->create();
        
        $model->load($memberproductId, 'product_id');
        
        $customerGroupId = $model->getRelatedCustomerGroupId();
        
        $rules = $this->_ruleCollectionFactory->create();
        
        $rule_data= [];
        
        $customerGroups = $this->_customerGroup->toOptionArray();
        foreach ($customerGroups as $group) {
            $allCustomerGroupId[] = $group['value'];
        }
       
        foreach ($rules as $rule) {
            if ($rule->getIsActive()) {
                $rule_dd = $this->_catalogRule->load($rule->getId());
                $customer_ids = $rule_dd->getData('customer_group_ids');
                if (in_array($customerGroupId, $customer_ids)) {
                    $rule_data[]  = $rule->getId();
                }
            }
        }
        
        foreach ($rule_data as $key => $value) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collections = $objectManager->create('Magento\CatalogRule\Model\Rule');
            $catalog_rule = $collections->load($value);
            $relatedProductIds[] = $catalog_rule->getMatchingProductIds();
        }

        $productIds = [];
        if (!empty($relatedProductIds)) {
            foreach ($relatedProductIds as $relatedProductId) {
                foreach ($relatedProductId as $productId => $product) {
                    if (isset($product) and ! empty($product)) {
                        if ($product[1] == 1) {
                            $productIds[] = $productId;
                        }
                    }
                }
            }
        }
        //$relatedProductCollection = $this->product->getRelatedProductIds();
        $relatedProductList = [];
        if (count($productIds) > 0) {
            $productCollection = $this->productFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addFieldToFilter("entity_id", ["in"=>$productIds])
                ->setPageSize(5)
                ->setCurPage(1);
            foreach ($productCollection as $eachProduct) {
                if ($eachProduct->isAvailable()) {
                    $relatedProductList[] = $this->helperCatalog->getOneProductRelevantData(
                        $eachProduct,
                        $this->storeId,
                        $this->width,
                        $this->customerId
                    );
                }
            }
        }
        $this->returnArray["discountableProductList"] = $relatedProductList;
        
        // $itemCollection = $this->productFactory->create()->getCollection()
        //         ->addAttributeToSelect(
        //             ('*')
        //         )->addAttributeToFilter(
        //             'entity_id',
        //             ['in' => $productIds]
        //         );
        
        
        // if ($this->moduleManager->isEnabled('Magento_Checkout')) {
        //     $this->_addProductAttributesAndPrices($itemCollection);
        // }
        // $itemCollection->setVisibility($this->productVisibility->getVisibleInCatalogIds());

        // foreach ($itemCollection as $product) {
        //     $product->setDoNotUseCategoryId(true);
        // }
        
        // if($itemCollection->getSize()){
        //     $itemData=[];
        //     foreach($itemCollection as $item){
        //         $productt = $this->productFactory->create()->load($item->getId());
        //         $galleryCollection = $productt->getMediaGalleryImages();
        //         $imageUrll =  "";
        //         foreach($galleryCollection as $image){
        //             $imageUrll = $this->imageHelper->init($productt, "product_page_image_small")
        //         ->keepFrame(false)->resize($this->width/3)->setImageFile($image->getFile())->getUrl();
        //         }
                
        //         $result=[
        //             "id" => $item->getId(),
        //             "name" => $item->getName(),
        //             "image_url" => $imageUrll,
        //             "price" => $item->getPrice()
        //         ];
        //         $itemData[]=$result;
        //     }
            
        //     $this->returnArray["discountableProductList"] = $itemData;
        // }
        
    }

    /**
     * Function getUpsellProduct
     *
     * @return void
     */
    protected function getUpsellProduct()
    {
        $upsellProductCollection = $this->product->getUpSellProductCollection()->setPositionOrder()->addStoreFilter();
        $upsellProductCollection->setVisibility(
            $this->productVisibility->getVisibleInSiteIds()
        )->addAttributeToSelect("*");
        $upsellProductList = [];
        $upsellProductCollection->setPageSize(5)->setCurPage(1);
        foreach ($upsellProductCollection as $eachProduct) {
            $upsellProductList[] = $this->helperCatalog->getOneProductRelevantData(
                $eachProduct,
                $this->storeId,
                $this->width,
                $this->customerId
            );
        }
        $this->returnArray["upsellProductList"] = $upsellProductList;
    }

    /**
     * Function getProductBasicDeatils
     *
     * @return void
     */
    protected function getProductBasicDeatils()
    {
        $this->returnArray["id"] = $this->productId;
        $this->returnArray["name"] = htmlspecialchars_decode($this->product->getName());
        $this->returnArray["typeId"] = $this->product->getTypeId();
        $this->returnArray["productUrl"] = $this->product->getProductUrl();
        $this->returnArray["guestCanReview"] = (bool)$this->helper
            ->getConfigData("catalog/review/allow_guest");
        $this->returnArray["showPriceDropAlert"] = (bool)$this->helper
            ->getConfigData("catalog/productalert/allow_price");
        $this->returnArray["showBackInStockAlert"] = (bool)$this->helper
            ->getConfigData("catalog/productalert/allow_stock");
        $this->returnArray["isAllowedGuestCheckout"] = (bool)$this->helper
            ->getConfigData("checkout/options/guest_checkout");

        $price = $this->product->getPrice();
        $finalPrice = $this->product->getFinalPrice();

        if ($this->product->getTypeId() == "bundle") {
            $bundlePriceModel = $this->bundlePriceModel;
            $this->returnArray["minPrice"] = $bundlePriceModel->getTotalPrices($this->product, "min", 1);
            $this->returnArray["maxPrice"] = $bundlePriceModel->getTotalPrices($this->product, "max", 1);
            $this->returnArray["formattedMinPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($bundlePriceModel->getTotalPrices($this->product, "min", 1))
            );
            $this->returnArray["formattedMaxPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($bundlePriceModel->getTotalPrices($this->product, "max", 1))
            );
            if ($this->product->getSpecialPrice()) {
                $price = $this->product->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
                $finalPrice = $this->product->getPriceInfo()->getPrice('final_price')->getValue();
            }
        } else {
            $this->returnArray["minPrice"] = $this->product->getMinPrice();
            $this->returnArray["maxPrice"] = $this->product->getMaxPrice();
            $this->returnArray["formattedMinPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($this->product->getMinPrice())
            );
            $this->returnArray["formattedMaxPrice"] = $this->helperCatalog->stripTags(
                $this->getPriceInCurrency($this->product->getMaxPrice())
            );
        }

        if ($this->product->getTypeId() == "configurable") {
            $regularPrice = $this->product->getPriceInfo()->getPrice("regular_price");
            $price = $regularPrice->getAmount()->getBaseAmount();
        } elseif (!empty($price)) {
            $price = $this->pricingHelper->currency($price, false, false);
            $finalPrice = $this->pricingHelper->currency($finalPrice, false, false);
        } elseif (empty($price)) {
            $price = 0.0;
        }
        $this->isIncludeTaxInPrice = false;
        if ($this->helper->getConfigData("tax/display/type") == 2) {
            $this->isIncludeTaxInPrice = true;
        }
        if ($this->isIncludeTaxInPrice) {
            $this->returnArray["price"] = $this->taxHelper->getTaxPrice($this->product, $price);
            $this->returnArray["finalPrice"] = $this->taxHelper->getTaxPrice($this->product, $finalPrice);
            $this->returnArray["specialPrice"] = $this->taxHelper->getTaxPrice(
                $this->product,
                $this->product->getSpecialPrice()
            );
            $this->returnArray["formattedPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($this->taxHelper->getTaxPrice($this->product, $price))
            );
            $this->returnArray["formattedFinalPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format(
                    $this->taxHelper->getTaxPrice($this->product, $this->product->getFinalPrice())
                )
            );
            $this->returnArray["formattedSpecialPrice"] = $this->helperCatalog->stripTags($this->priceCurrency->format(
                $this->taxHelper->getTaxPrice($this->product, $this->product->getSpecialPrice())
            ));
        } else {
            $this->returnArray["price"] = $price;
            $this->returnArray["finalPrice"] = $finalPrice;
            $this->returnArray["specialPrice"] = $this->product->getSpecialPrice();
            $this->returnArray["formattedPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($price)
            );
            $this->returnArray["formattedFinalPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($finalPrice)
            );
            $this->returnArray["formattedSpecialPrice"] = $this->helperCatalog->stripTags(
                $this->priceCurrency->format($this->product->getSpecialPrice())
            );
        }
        $this->returnArray["msrp"] = $this->product->getMsrp();
        $this->returnArray["msrpEnabled"] = $this->product->getMsrpEnabled();
        if ($this->product->getDescription() != "") {
            $this->returnArray["description"] = $this->filterProvider->getBlockFilter()->filter(htmlspecialchars_decode(
                $this->product->getDescription()
            ));
        } else {
            $this->returnArray["description"] = "";
        }
        $formattedMsrp = $this->getPriceInCurrency($this->product->getMsrp());
        $this->returnArray["formattedMsrp"] = $this->helperCatalog->stripTags($formattedMsrp);
        $this->returnArray["shortDescription"] = $this->filterProvider->getBlockFilter()
            ->filter(htmlspecialchars_decode($this->product->getShortDescription()));
        $this->returnArray["msrpDisplayActualPriceType"] = $this->product->getMsrpDisplayActualPriceType();
        $this->checkIsInRange();
        if ($this->product->isAvailable()) {
            $this->returnArray["availability"] = __("In stock");
            $this->returnArray["isAvailable"] = true;
        } else {
            $this->returnArray["availability"] = __("Out of stock");
            $this->returnArray["isAvailable"] = false;
        }
    }

    /**
     * Function getPriceInCurrency
     *
     * @param float $price price
     *
     * @return string
     */
    protected function getPriceInCurrency($price)
    {
        return $this->pricingHelper->currency($price);
    }
}
