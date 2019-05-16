"use strict";

let express = require('express');
let router = express.Router();
var createError = require('http-errors');

/* POST notifications */
router.post('/send', function (req, res, next) {
    console.log(req.body.event);
    let clients = req.app.get('clients');

    let events = ['request', 'order-paid'];
    let types = ['info', 'success', 'error', 'warning'];

    if (   req.body.event !== undefined
        && req.body.title !== undefined
        && req.body.text !== undefined
        && events.indexOf(req.body.event) !== -1
    ) {
        let recipients = (Array.isArray(req.body.recipients)) ? req.body.recipients : [];
        let ignore = (Array.isArray(req.body.ignore)) ? req.body.ignore : [];

        console.log('Clients: ' + clients.length);

        clients.forEach(function (client) {
            let now = new Date();
            let isEmit = true;

            if (ignore.length > 0 && ignore.indexOf(client.id) !== -1) {
                isEmit = false;
                console.log('Ignored: ', client.id);
            }

            if (isEmit && (recipients.length === 0 || recipients.indexOf(client.id) !== -1)) {

                console.log('Emited to: ', client.id);
                console.log(req.params, req.body);

                client.emit('notification', {
                    'event': req.body.event,
                    'type': (types.indexOf(req.body.type) !== -1) ? req.body.type : 'info',
                    'title': req.body.title,
                    'text': req.body.text
                });

                if (req.body.event === 'request' || req.body.event === 'order-paid') {
                    client.emit('pipeline-updated', 'requests');
                }
            }
        });

        res.end();
    } else {
        next(createError(400));
    }
});

module.exports = router;
