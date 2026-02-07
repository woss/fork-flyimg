<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
    <title><?= $title; ?></title>
    <link rel="stylesheet" href="/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/main.css" crossorigin="anonymous">
    <link href="css/main.css" rel="stylesheet">
</head>
<body>
<div class="site-wrapper container-md">
    <div class="cover-container">
        <div class="inner cover">
            <div class="inner cover">
                <hr>
                <p>
                    <img alt="Flyimg"
                         src="/upload/w_300/https://raw.githubusercontent.com/flyimg/flyimg/main/web/flyimg.png">
                </p>
                <h1><?= $statusCode; ?> <?= $statusText; ?></h1>
                <h2>The server returned:</h2>
                <p id="exception-message" class="error-message"><?= $exceptionMessage; ?></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
