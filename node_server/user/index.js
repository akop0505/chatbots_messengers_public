/**
 * Created by pc4 on 25/01/2019.
 */
"use strict";

// var DB = require('../dba');

module.exports = function (clients) {

    return {
        /**
         * Функция производит проверку ключа и если он валиден
         * то она добавляет пользователя в стек лист
         */
        checkUserViaKey: function(client) {
            clients.push(client);
            // let _that = this;
            //
            // DB.query("SELECT * FROM sf_users WHERE auth_id = '" + client.auth_key + "'", null, function (data, error) {
            //     _that.addUser(data, client, error);
            // });
        },

        /**
         * Добавление обычного юзера в список клиентов.
         * @param result
         * @param client
         * @param error
         * @returns {boolean}
         */
        addUser: function (result, client, error) {
            if(result === null || result.length <= 0) {
                return false;
            }

            let user = result[0];

            if (user.id > 0) {
                clients.every(
                    function (item, index, arr) {
                        if (clients[index].auth_key === client.auth_key) {
                            clients.splice(index, 1);
                        }

                        return true;
                    });

                client.auth = true;
                client.id = user.id;

                clients.push(client);
                client.emit('connected', user.id);

                console.log('Joined:', user.id);
            }

            return false;
        }
    };
};