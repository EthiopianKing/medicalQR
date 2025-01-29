const http = require("http");
const fs = require("fs");
const bcrypt = require('bcrypt');
const socketio = require("socket.io");

const port = 3456;
const file = "client.html";
const saltRounds = 10;

let users = [];
let chatRooms = [
    { room_id: 1, room_name: "Main Lobby", is_private: false, password: null, creator_id: null, bannedUsers: [] }
];
let messages = [];

let userIdCounter = 1;
let roomIdCounter = 2;
let messageIdCounter = 1;

const server = http.createServer(function (req, res) {
    fs.readFile(file, function (err, data) {
        if (err) {
            res.writeHead(500);
            return res.end("Error loading client.html");
        }
        res.writeHead(200);
        res.end(data);
    });
});
server.listen(port);

const io = socketio(server);

io.sockets.on("connection", function (socket) {
    socket.on('register', function (data) {
        bcrypt.hash(data.password, saltRounds, function (err, hash) {
            if (err) {
                socket.emit("registration_failed", err.message);
                return;
            }
            const newUser = {
                user_id: userIdCounter++,
                username: data.username,
                password: hash,
                email: data.email,
                socket_id: socket.id
            };
            users.push(newUser);
            socket.emit("registration_success", "Registration successful");
        });
    });

    socket.on('login', function (data) {
        const user = users.find(u => u.username === data.username);
        if (!user) {
            socket.emit("login_failed", "Username does not exist");
            return;
        }
        bcrypt.compare(data.password, user.password, function (err, result) {
            if (result) {
                user.socket_id = socket.id;
                socket.emit("login_success", { userId: user.user_id });
                joinRoom(socket, chatRooms[0], user.user_id);

            } else {
                socket.emit("login_failed", "Password is incorrect");
            }
        });
    });

    socket.on('create_room', function (data) {
        bcrypt.hash(data.password, saltRounds, function (err, hash) {
            if (err) {
                socket.emit("room_creation_failed", err.message);
                return;
            }
            const newRoom = {
                room_id: roomIdCounter++,
                room_name: data.roomName,
                is_private: data.isPrivate,
                password: data.isPrivate ? hash : null,
                creator_id: data.userId,
                bannedUsers: []
            };
            chatRooms.push(newRoom);
            socket.join(newRoom.room_name);
            socket.emit("room_creation_success", { roomId: newRoom.room_id, roomName: newRoom.room_name });
        });
    });

    socket.on('check_room_privacy', function (data) {
        const room = chatRooms.find(r => r.room_id === parseInt(data.roomId));
        if (!room) {
            socket.emit("room_join_failed", "Room does not exist");
            return;
        }
        socket.emit("room_privacy_status", { roomId: room.room_id, isPrivate: room.is_private });
    });



    socket.on('join_room', function (data) {
        const room = chatRooms.find(r => r.room_id === parseInt(data.roomId));
        if (!room) {
            socket.emit("room_join_failed", "Room does not exist");
            return;
        }
        if (room.is_private) {
            bcrypt.compare(data.password, room.password, function (err, result) {
                if (result) {
                    joinRoom(socket, room, data.userId);
                } else {
                    socket.emit("room_join_failed", "Incorrect password");
                }
            });
        } else {
            joinRoom(socket, room, data.userId);
        }
    });

    socket.on('message_to_server', function (data) {
        const user = users.find(u => u.user_id === data.userId);
        if (!user) {
            socket.emit("message_failed", "User not found");
            return;
        }
        const newMessage = {
            message_id: messageIdCounter++,
            user_id: data.userId,
            room_id: data.roomId,
            message_text: data.message,
            timestamp: new Date()
        };
        messages.push(newMessage);
        const username = user.username;
        io.to(data.roomId.toString()).emit("message_to_client", { roomId: data.roomId, message: username + ": " + data.message });
    });

    socket.on('ban_user', function (data) {
        const room = chatRooms.find(r => r.room_id === data.roomId);
        const userToBan = users.find(u => u.username === data.username);
        if (room && userToBan) {
            room.bannedUsers.push(userToBan.user_id);
            const bannedUserSocket = io.sockets.sockets.get(userToBan.socket_id);
            if (bannedUserSocket) {
                bannedUserSocket.leave(room.room_name);
                bannedUserSocket.emit("you_have_been_banned", { mainLobbyId: chatRooms[0].room_id, roomName: room.room_name });
            }
            socket.emit("user_banned", { username: data.username });
        } else {
            socket.emit("ban_failed", "Could not find room or user to ban.");
        }
    });

    socket.on('kick_user', function (data) {
        const room = chatRooms.find(r => r.room_id === data.roomId);
        const userToKick = users.find(u => u.username === data.username);
        if (room && userToKick) {
            const userSocket = io.sockets.sockets.get(userToKick.socket_id);
            if (userSocket) {
                userSocket.leave(room.room_name);
                userSocket.emit("you_have_been_kicked", { mainLobbyId: chatRooms[0].room_id });
                socket.emit("user_kicked", { username: data.username });
            } else {
                socket.emit("kick_failed", "Could not find user socket to kick.");
            }
        } else {
            socket.emit("kick_failed", "Could not find room or user to kick.");
        }
    });

    socket.on('private_message', function (data) {
        const sender = users.find(u => u.user_id === data.senderUserId);
        const recipient = users.find(u => u.username === data.recipientUsername);

        if (!sender) {
            socket.emit("private_message_failed", "Sender not found");
            return;
        }

        if (!recipient) {
            socket.emit("private_message_failed", "Recipient not found");
            return;
        }

        const recipientSocket = io.sockets.sockets.get(recipient.socket_id);
        if (recipientSocket) {
            recipientSocket.emit("private_message_received", {
                senderUsername: sender.username,
                message: data.message,
            });
        }
    });
});

function joinRoom(socket, room, userId) {
    if (room.bannedUsers.includes(userId)) {
        socket.emit("join_room_failed", "You are banned from this room.");
        return;
    }
    socket.join(room.room_id.toString());
    const roomMessages = messages.filter(m => m.room_id === room.room_id);
    const messageHistory = roomMessages.map(m => {
        const user = users.find(u => u.user_id === m.user_id);
        return { username: user.username, message_text: m.message_text };
    });
    const isCreator = room.creator_id === userId;
    socket.emit("room_join_success", { roomId: room.room_id, roomName: room.room_name, messages: messageHistory, isCreator: isCreator });
}

console.log(`Server running at http://localhost:${port}/`);
