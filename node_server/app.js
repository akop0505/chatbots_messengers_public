"use strict";

var env_file = '.env';
if (process.env.NODE_ENV === 'dev') {
    env_file = '.env.dev';
}

require('dotenv').config({'path': env_file});

var express = require('express');
var http = require('http');
var bodyParser = require('body-parser');
var createError = require('http-errors');
var clients = [];

var app = express();

app.use(express.json());
app.use(express.urlencoded({ extended: false }));
app.use(bodyParser.json());

app.use(function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

app.use('/', require('./routes/index'));
app.use('/notification', require('./routes/notification'));
app.use('/call', require('./routes/call'));

app.use(function(req, res, next) {
    next(createError(404));
});

app.use(function(err, req, res, next) {
    res.locals.message = err.message;
    res.locals.error = process.env.NODE_ENV === 'dev' ? err : {"message": "error"};

    res.status(err.status || 500);
    res.send(res.locals.error);
});

var server = http.createServer(app).listen(process.env.PORT, function() {
    console.log('Express server listening on port ' + process.env.PORT);
    console.log('Environment: ' + process.env.NODE_ENV);
});

var io = require('./socket')(server, clients);

app.set('io', io);
app.set('clients', clients);

module.exports = app;
