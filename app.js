/**
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

    app.listen(1362, function () {
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
  });