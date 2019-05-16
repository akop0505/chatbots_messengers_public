"use strict";

var socket = require('socket.io');

module.exports = function (server, clients) {
    var options = {
        pingInterval:2000,
        pingTimeout:10000
    };

    var user = require('../user')(clients);
    var io = socket.listen(server, options);

    io.sockets.on('connection', function (client) {
        console.log(client);
        client.auth = false;
        client.auth_key = undefined;
        client.id = undefined;

        client.on('join', function (data) {
            let json_data = JSON.parse(data);
            if(json_data['auth_key'] === 'undefined')
            {
                console.log('Auth Key is empty');
                return false;
            }

            client.auth_key = json_data['auth_key'];

            user.checkUserViaKey(client);
        });

        client.on('disconnect', function (data) {
            console.log('Client disconnected: ' + client.id);
            clients.every(
                function (item, index, arr){
                    if (clients[index].auth_key === client.auth_key) {
                        clients.splice(index, 1);
                    }

                    return true;
                });
        });

        client.on('pipeline-entity-moved', function (type) {
            console.log(type + ' is moved');

            client.broadcast.emit('pipeline-updated', type);
        });

    });

    return io;
}