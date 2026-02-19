<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        <!-- Tailwind CSS (CDN) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
        <style>
            html,body{height:100%;margin:0;padding:0}
            body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial;background:#fafafa}
            a {text-decoration: none}
            .soft-card{background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.12);border:1px solid #e5e7eb}
            .soft-btn{transition:all 0.2s ease}
            .soft-btn:hover{opacity:0.8}
            .soft-input{background:#fff;border:1px solid #dbdbdb;transition:border 0.2s ease}
            .soft-input:focus{border-color:#a8a8a8;outline:none}
        </style>

        <!-- Vue (global) -->
        <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>
</body>
</html>
