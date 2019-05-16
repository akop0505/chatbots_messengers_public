"use strict";

// var env_file = '.env';
//console.log(process.env);
/*if (process.env.NODE_ENV === 'dev') {
    env_file = '.env.dev';
}*/

// require('dotenv').config({'path': env_file});

const express = require('express');
const  http = require('http');
const  bodyParser = require('body-parser');
const  createError = require('http-errors');
let  clients = [];

const app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(bodyParser.json());

app.use(function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

app.use('/', require('./routes/index'));
// app.use('/notification', require('./routes/notification'));
// app.use('/call', require('./routes/call'));

app.use(function(req, res, next) {
    next(createError(404));
});

app.use(function(err, req, res, next) {
    res.locals.message = err.message;
    res.locals.error = err;

    res.status(err.status || 500);
    res.send(res.locals.error);
});

const  server = http.createServer(app).listen(3001, function() {
    console.log('Express server listening on port ' + 3001);
    // console.log('Environment: ' + process.env.NODE_ENV);
});

var io = require('./socket')(server, clients);

app.set('io', io);
app.set('clients', clients);

module.exports = app;
