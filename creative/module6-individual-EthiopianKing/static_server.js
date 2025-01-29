var http = require('http'),
    url = require('url'),
    mime = require('mime'),
    path = require('path'),
    fs = require('fs');

var app = http.createServer(function (req, resp) {
    var filename = path.join(__dirname, "static", url.parse(req.url).pathname);
    (fs.exists || fs.existsSync)(filename, function (exists) {
        if (exists) {
            fs.readFile(filename, function (err, data) {
                if (err) {
                    resp.writeHead(500, {
                        "Content-Type": "text/plain"
                    });
                    resp.write("Internal server error: could not read file");
                    resp.end();
                } else {
                    var mimetype = mime.getType(filename);
                    resp.writeHead(200, {
                        "Content-Type": mimetype
                    });
                    resp.write(data);
                    resp.end();
                }
            });
        } else {
            resp.writeHead(404, {
                "Content-Type": "text/plain"
            });
            resp.write("Requested file not found: " + filename);
            resp.end();
        }
    });
});
app.listen(3456);
