<?php
/**
 * Copyright (c) 2019 Venustheme
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Ves\Megamenu\Model;

class FlushcacheMegamenu implements \Ves\Megamenu\Api\FlushcacheManagementInterface
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Cache
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource 
     * @param \Magento\Framework\App\CacheInterface $cache
     * @api
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\CacheInterface $cache
        ) {
        $this->_resource = $resource;
        $this->_cache = $cache;
    }
    /**
     * {@inheritdoc}
     */
    public function execute(): string
    {
        try {
            $resource   = $this->_resource;
            $table      = $resource->getTableName('ves_megamenu_cache');
            $connection = $resource->getConnection();
            $connection->truncateTable($table);
            $this->_cache->clean([\Ves\Megamenu\Model\Menu::CACHE_HTML_TAG]);
            return __("The Mega Menu Cache has been flushed.");
        } catch (\Exception $e) {
            return __("Something went wrong in progressing.")."\n".$e->getMessage();
        }
    }

}
