<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;

class DashboardMiddleColumnController extends AppController
{
    public function index()
    {
        $this->viewBuilder()->disableAutoLayout();

        // Pagination params for AJAX loading
        $start = (int)$this->request->getQuery('start', 0);
        $limit = 20;

        // Try to load posts from Posts model if it exists
        $posts = [];
        if ($this->getTableLocator()->exists('Posts')) {
            $postsTable = $this->getTableLocator()->get('Posts');
            $posts = $postsTable->find()
                ->orderDesc('created')
                ->limit($limit)
                ->offset($start)
                ->toArray();
        }

        $this->set(compact('posts','start','limit'));
        // Render the existing template under templates/MiddleColumn/index.php
        return $this->render('/MiddleColumn/index');
    }
}
