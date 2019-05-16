"use strict";

let express = require('express');
let router = express.Router();

router.get('/', function (req, res, next) {
    console.log(req.body.title);
    let clients = req.app.get('clients');
    console.log(clients);
    res.send('Push server is started!');
    res.end();
});

module.exports = router;
