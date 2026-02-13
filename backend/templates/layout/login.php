<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $pageTitle = $this->fetch('title');
    $controller = strtolower($this->getRequest()->getParam('controller'));
    if (empty($pageTitle) || $controller === 'dashboard' || strtolower($pageTitle) === 'dashboard') {
        $fullTitle = 'WeLinked';
    } else {
        $fullTitle = 'WeLinked - ' . $pageTitle;
    }
    ?>
    <title><?= h($fullTitle) ?></title>
    <link rel="icon" href="/favicon.ico" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Global Styles -->
    <?= $this->Html->css('global') ?>
    
    <!-- Vue.js 3 CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
</body>
</html>
