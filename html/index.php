<?php
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib.php';
    $error = '';
    if (isset($_GET['error'])) {
        $error = 'There was an error processing your request.';
    }
    if (isset($_FILES['file'])) {
        handleUpload();
    }
    if (isset($_GET['id'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            deleteEntry($_GET['id']);
            header('Location: /');
            exit;
        }
        $entry = getEntry($_GET['id'] ?? null);
        if ($entry) {
            $entry['id'] = $_GET['id'];
            $title = htmlspecialchars($entry['title'] ?? 'Untitled');
        } else {
            header('HTTP/1.0 404 Not Found');
            $error = 'Sorry, the content you are looking for does not exist.';
        }
    } else {
        $entry = null;
        $title = 'List of uploaded H5P content';
        $entries = getAllEntries();
    }
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">

        <title>H5P Standalone</title>

        <meta name="description" content="$description$">
        <meta name="author" content="Stephan Robotta">

        <meta property="og:description" content="Standalone site for uploading serving H5P content">
        <meta property="og:author" content="Stephan Robotta">

        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <script type="text/javascript" src="dist/main.bundle.js"></script>
        <!-- Define here the left and right footer container css. -->
        <style>
            * {
                font-family: Arial, sans-serif;
            }
            div.header {
                background-color: #ffcb3e;
                width: 100%;
                height: 120px;
            }
            div.header img {
                margin: 10px 30px 0 10px;
                width: 161px;
                height: 77px;
                float: left;
            }
            div.header .title {
                padding-top: 66px;
                margin: 0;
                color: #fff;
                font-weight: 700;
            }
            div.header .title a {
                color: #fff;
            }
            a {
                color: #4b647d;
            }
            .container {
                max-width: 850px;
                margin: auto;
            }
            .error {
                color: red;
            }
        </style>
    </head>

    <body>

        <div class="header"><a href="/"><img src="h5p.svg" /></a>
            <div class="title">
                H5P is a trademark of <a href="https://h5p.org">H5P Group</a>.
                This site uses a <a href="https://github.com/tunapanda/h5p-standalone">standalone library</a>
                assembled by <a href="https://tunapanda.org">Tunapanda Institute</a>.
            </div>            
        </div>
        <div class="container">
            <p class="error"><?= $error ?></p>
            <h1><?= $title ?></h1>
            <div>
                <div id="h5p-container"></div>
            </div>
        
            <?php if ($entry) { ?>
                <script>
                const el = document.getElementById('h5p-container');
                const options = {
                    h5pJsonPath:  '<?= WEB_DATA_DIR . '/' . $entry['id']; ?>',
                    frameJs: '/dist/frame.bundle.js',
                    frameCss: '/dist/styles/h5p.css',
                };
                new H5PStandalone.H5P(el, options);
                </script>
            <?php } else { ?>
                <ul>
                    <?php foreach ($entries as $id => $entry) { ?>
                        <li>
                            <a href="?id=<?= $id ?>"><?= htmlspecialchars($entry['title'] ?? 'Untitled') ?></a>
                        </li>
                    <?php } ?>
                </ul>
                <p>Upload a new H5P file:</p>
                <form method="post" enctype="multipart/form-data">
                    <input type="file" name="file" />
                    <input type="submit" value="Upload" />
                </form>
            <?php } ?>
        </div>
    </body>
</html>
