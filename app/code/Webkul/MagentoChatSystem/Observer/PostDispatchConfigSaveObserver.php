<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MagentoChatSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Module\Dir\Reader;

/**
 * Webkul PostDispatchConfigSaveObserver Observer.
 */
class PostDispatchConfigSaveObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ManagerInterface $messageManager
     * @param Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        IoFile $iofile,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->fileDriver = $fileDriver;
        $this->iofile = $iofile;
        $this->directoryList = $directoryList;
        $this->moduleReader = $moduleReader;
        $this->_baseDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $etcDir = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                'Webkul_MagentoChatSystem'
            );
            /** @var \Magento\Framework\ObjectManagerInterface $objManager */
            $objManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Magento\Framework\Module\Dir\Reader $reader */
            $reader = $objManager->get(Reader::class);

            /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = $objManager->get(Filesystem::class);

            $observerRequestData = $observer['request'];
            $params = $observerRequestData->getParams();
            if ($params['section'] == 'chatsystem') {
                $paramsData = $params['groups']['chat_config']['fields'];
                if (isset($paramsData['port_number']['value']) && $paramsData['port_number']['value']) {
                    $baseDirPath = $this->_baseDirectory->getAbsolutePath();
                    $manifestFile = $this->fileDriver->fileOpen($baseDirPath."/app.js", "w");
                    if ($this->isCurrentlySecure()) {
                        $manifestFileData =

                        '/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var roomUsers  = {};
var customerRoom = "customerRoom";
var adminRoom = adminRoom;
var siofu = require("socketio-file-upload");
var fs = require("fs");
var https = require("https");
var options_https = {
    key: fs.readFileSync("pub/media/server_files/default/server.key", "utf8"),
    cert: fs.readFileSync("pub/media/server_files/default/server.crt", "utf8"),
    ca: fs.readFileSync("pub/media/server_files/default/server.ca-bundle"),
    requestCert: true,
    rejectUnauthorized: false
};
    
    var app = https.createServer(options_https);
    var io = require("socket.io")(app);

    app.listen('.$paramsData["port_number"]["value"].', function () {
        console.log("listening");
    });

    io.on("connection", function (socket) {

        var uploader = new siofu();

        // Do something when a file is saved:
        uploader.on("saved", function (event) {
            event.file.clientDetail.fileName = event.file.name;
            event.file.clientDetail.savedFileName = event.file.pathName.split("/").pop();
        });
    
        // Error handler:
        uploader.on("error", function (event) {
            console.log("Error from uploader", event);
        });
    
        uploader.uploadValidator = function (event, callback) {
            fs.mkdir("pub/media/chatsystem/attachments", function (err, folder) {
                if (err) {
                    if (err.code == "EEXIST") {
                        uploader.dir = err.path;
                        callback(true);
                    } else {
                        callback(false); // abort
                    }
                } else {
                    uploader.dir = folder;
                    callback(true); // ready
                }
            });
        };
    
        uploader.listen(socket);

        socket.on("newUserConneted", function (details) {
          if (details.sender === "admin") {
            var index = details.sender+"_"+details.adminId;
            roomUsers[index] = socket.id;
          } else if (details.sender === "customer") {
            var index = details.sender+"_"+details.customerId;
            roomUsers[index] = socket.id;
            Object.keys(roomUsers).forEach(function (key, value) {
                if (key === "admin_"+details.receiver) {
                  receiverSocketId = roomUsers[key];
                  socket.broadcast.to(receiverSocketId).emit("refresh admin chat list", details);
                }
            });
          }
        });

        socket.on("newCustomerMessageSumbit", function (data) {
          var isSupportActive = true;
          if (typeof(data) !== "undefined") {
            Object.keys(roomUsers).forEach(function (key, value) {
                if (key === "admin_"+data.receiver) {
                  isSupportActive = true;
                  receiverSocketId = roomUsers[key];
                  socket.broadcast.to(receiverSocketId).emit("customerMessage", data);
                }
            });
            if (!isSupportActive) {
              receiverSocketId = roomUsers["customer_"+data.sender];
              socket.broadcast.to(receiverSocketId).emit("supportNotActive", data);
            }
          }
      });
      socket.on("newAdminMessageSumbit", function (data) {
          if (typeof(data) !== "undefined") {
           Object.keys(roomUsers).forEach(function (key, value) {
                if (key === "customer_"+data.receiver) {
                  receiverSocketId = roomUsers[key];
                  socket.broadcast.to(receiverSocketId).emit("adminMessage", data);
                }
            });
          }
      });
      socket.on("updateStatus", function (data) {
          var isSupportActive = true;
          if (typeof(data) !== "undefined") {
            Object.keys(roomUsers).forEach(function (key, value) {
                if (key === "admin_"+data.receiver) {
                  receiverSocketId = roomUsers[key];
                  socket.broadcast.to(receiverSocketId).emit("customerStatusChange", data);
                }
            });
          }
      });

      socket.on("admin status changed", function (data) {
          if (typeof(data) !== "undefined") {
           Object.keys(roomUsers).forEach(function (key, value) {
            Object(data.receiverData).forEach(function (k) {
                if (key === "customer_"+k.customerId) {
                    receiverSocketId = roomUsers[key];
                    socket.broadcast.to(receiverSocketId).emit("adminStatusUpdate", data.status);
                }
                });
            });
          }
      });
  });';
                    } else {
                        $manifestFileData =
                        '
  /**
   * Webkul Software.
   *
   * @category  Webkul
   * @package   Webkul_MagentoChatSystem
   * @author    Webkul
   * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
   * @license   https://store.webkul.com/license.html
   */
  var app = require("http").createServer();
  var siofu = require("socketio-file-upload");
  var fs = require("fs");
  var io = require("socket.io")(app);
  var roomUsers  = {};
  var customerRoom = "customerRoom";
  var adminRoom = "adminRoom";

  app.listen('.$paramsData["port_number"]["value"].', function () {
      console.log("listening");
  });

  io.on("connection", function (socket) {
    var uploader = new siofu();

    // Do something when a file is saved:
    uploader.on("saved", function (event) {
        event.file.clientDetail.fileName = event.file.name;
        event.file.clientDetail.savedFileName = event.file.pathName.split("/").pop();
    });

    // Error handler:
    uploader.on("error", function (event) {
        console.log("Error from uploader", event);
    });

    uploader.uploadValidator = function (event, callback) {
        fs.mkdir("pub/media/chatsystem/attachments", function (err, folder) {
            if (err) {
                if (err.code == "EEXIST") {
                    uploader.dir = err.path;
                    callback(true);
                } else {
                    callback(false); // abort
                }
            } else {
                uploader.dir = folder;
                callback(true); // ready
            }
        });
    };

    uploader.listen(socket);

      socket.on("newUserConneted", function (details) {
        if (details.sender === "admin") {
          var index = details.sender+"_"+details.adminId;
          roomUsers[index] = socket.id;
        } else if (details.sender === "customer") {
          var index = details.sender+"_"+details.customerId;
          roomUsers[index] = socket.id;
          Object.keys(roomUsers).forEach(function (key, value) {
              if (key === "admin_"+details.receiver) {
                receiverSocketId = roomUsers[key];
                socket.broadcast.to(receiverSocketId).emit("refresh admin chat list", details);
              }
          });
        }
      });

      socket.on("newCustomerMessageSumbit", function (data) {
        var isSupportActive = true;
        if (typeof(data) !== "undefined") {
          Object.keys(roomUsers).forEach(function (key, value) {
              if (key === "admin_"+data.receiver) {
                isSupportActive = true;
                receiverSocketId = roomUsers[key];
                socket.broadcast.to(receiverSocketId).emit("customerMessage", data);
              }
          });
          if (!isSupportActive) {
            receiverSocketId = roomUsers["customer_"+data.sender];
            socket.broadcast.to(receiverSocketId).emit("supportNotActive", data);
          }
        }
    });
    socket.on("newAdminMessageSumbit", function (data) {
        if (typeof(data) !== "undefined") {
         Object.keys(roomUsers).forEach(function (key, value) {
              if (key === "customer_"+data.receiver) {
                receiverSocketId = roomUsers[key];
                socket.broadcast.to(receiverSocketId).emit("adminMessage", data);
              }
          });
        }
    });
    socket.on("updateStatus", function (data) {
        var isSupportActive = true;
        if (typeof(data) !== "undefined") {
          Object.keys(roomUsers).forEach(function (key, value) {
              if (key === "admin_"+data.receiver) {
                receiverSocketId = roomUsers[key];
                socket.broadcast.to(receiverSocketId).emit("customerStatusChange", data);
              }
          });
        }
    });

    socket.on("admin status changed", function (data) {
        if (typeof(data) !== "undefined") {
         Object.keys(roomUsers).forEach(function (key, value) {
          Object(data.receiverData).forEach(function (k) {
              if (key === "customer_"+k.customerId) {
                  receiverSocketId = roomUsers[key];
                  socket.broadcast.to(receiverSocketId).emit("adminStatusUpdate", data.status);
              }
              });
          });
        }
    });
  });';
                    }
                    $this->fileDriver->fileWrite($manifestFile, $manifestFileData);
                    $this->fileDriver->fileClose($manifestFile);
                }
                $attachmentDir = $this->directoryList->getPath('media').'/chatsystem/attachments';
                if (!$this->fileDriver->isExists($attachmentDir)) {
                    $this->iofile->mkdir($attachmentDir);
                }
                $cspWhitelistPath = $etcDir.'/csp_whitelist.xml';
                $cspWhitelist = simplexml_load_file($cspWhitelistPath);
                $socket = 'http://'.$paramsData['host_name']['value'].':'.$paramsData['port_number']['value'].'/socket.io/';
                $sockets = 'https://'.$paramsData['host_name']['value'].':'.$paramsData['port_number']['value'].'/socket.io/';
                $websocket = 'ws://'.$paramsData['host_name']['value'].':'.$paramsData['port_number']['value'].'/socket.io/';
                $cspWhitelist->policies->policy[0]->values->value[0]=$socket;
                $cspWhitelist->policies->policy[0]->values->value[1]=$sockets;
                $cspWhitelist->policies->policy[0]->values->value[2]=$websocket;
                $cspWhitelist->asXML($cspWhitelistPath);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Check if current requested URL is secure
     *
     * @return boolean
     */
    protected function isCurrentlySecure()
    {
        return $this->storeManager->getStore()->isCurrentlySecure();
    }
}
