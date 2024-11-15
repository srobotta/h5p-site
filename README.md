# H5P Standalone Hosting

This project is an example on how to host H5P files on a self hosted system.

This is a rather simple setup. A single `index.php` is serving all requests. Files
can be uploaded, then displayed. There is no user authentication so anyone can view
add or delete entries.

Deleting an entry must be done via the command line. The URL is the
same as view a single file but with the HTTP method DELETE. In curl this can be done like:

```
curl -XDELETE http://localhost:8080/?id=MCZzw5nTXX
```

This project was done with the help of the [standalone library](https://github.com/tunapanda/h5p-standalone)
assembled by the [Tunapanda Institute](https://tunapanda.org).

Run the server from this root directory by executing:

```
php -S localhost:80 -t html -d upload_max_filesize=100M -d post_max_size=100M
```

If you get an error about permission denied, then use an upper port such as 8080 or run
the command with root privileges.