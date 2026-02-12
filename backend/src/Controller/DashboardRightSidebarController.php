<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;

class DashboardRightSidebarController extends AppController
{
    public function index()
    {
        $this->viewBuilder()->disableAutoLayout();

        // Example suggested users: load from Users table if available
        $suggested = [];
        if ($this->getTableLocator()->exists('Users')) {
            $usersTable = $this->getTableLocator()->get('Users');
            $suggested = $usersTable->find()
                ->limit(5)
                ->toArray();
        }

        $currentUser = $this->request->getAttribute('identity');
        $this->set(compact('suggested','currentUser'));
        // Render the existing template under templates/RightSidebar/index.php
        return $this->render('/RightSidebar/index');
    }
}
