<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;

class DashboardLeftSidebarController extends AppController
{
    public function index()
    {
        // Render component HTML only (no layout) so dashboard can fetch it via AJAX
        $this->viewBuilder()->disableAutoLayout();

        // Example data: current user info could be pulled from Auth
        $currentUser = $this->request->getAttribute('identity');
        $this->set(compact('currentUser'));
        // Render the existing template under templates/LeftSidebar/index.php
        return $this->render('/LeftSidebar/index');
    }
}
