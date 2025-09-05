<!-- <?= $_message = sprintf('%s (%d %s)', $exceptionMessage, $statusCode, $statusText); ?> -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="<?= $this->charset; ?>" />
        <meta name="robots" content="noindex,nofollow" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title><?= $_message; ?></title>
        <link rel="icon" type="image/png" href="<?= $this->include('Errors/assets/images/favicon.png.base64'); ?>" />
        <style><?= $this->include('Errors/assets/css/exception.css'); ?></style>
        <style><?= $this->include('Errors/assets/css/exception_full.css'); ?></style>
    </head>
    <body>
        <script>
            document.body.classList.add(
                localStorage.getItem('symfony/profiler/theme') 
                    || (matchMedia('(prefers-color-scheme: dark)').matches 
                        ? 'theme-dark' : 'theme-light')
            );
        </script>

        <?php if (class_exists(\Symfony\Component\HttpKernel\Kernel::class)) { ?>
            <header>
                <div class="container">
                    <h1 class="logo"><?= $this->include('Errors/assets/images/logo.svg'); ?> Flyimg Exception</h1>

                </div>
            </header>
        <?php } ?>

        <?= $this->include('Errors/exception.html.php', $context); ?>

        <script>
            <?= $this->include('Errors/assets/js/exception.js'); ?>
        </script>
    </body>
</html>
<!-- <?= $_message; ?> -->
