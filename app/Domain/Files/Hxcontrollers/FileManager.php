<?php

namespace Leantime\Domain\Files\Hxcontrollers;

use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Files\Services\Files as FileService;

use Leantime\Core\Controller\HtmxController;



class FileManager extends HtmxController
{

  protected static string $view = 'files::components.file-manager';


  private FileRepository $filesRepo;
  private FileService $filesService;
  /**
   * Controller constructor
   *
   */
  public function init(FileRepository $filesRepo, FileService $filesService): void
  {
    $this->filesRepo = $filesRepo;
    $this->filesService = $filesService;
  }



  public function get($params): void
  {
    $module = $params['module'];
    $moduleId = $params['moduleId'];
    $currentModule = session('currentProject');

    
    if ($module === 'project') {
      $moduleId = session('currentProject');
    }

    if (isset($_POST['upload']) || isset($_FILES['file'])) {
      if (isset($_FILES['file'])) {
        $this->filesRepo->upload($_FILES, $module, session('currentProject'));
        // $this->tpl->setNotification('notifications.file_upload_success', 'success', 'file_created');
      } else {
        // $this->tpl->setNotification('notifications.file_upload_error', 'error');
      }
    }

    if (isset($_GET['delFile']) === true) {
      $result = $this->filesService->deleteFile($_GET['delFile']);

      if ($result === true) {
        // $this->tpl->setNotification($this->language->__('notifications.file_deleted'), 'success', 'file_deleted');

        // return Frontcontroller::redirect(BASE_URL . '/files/showAll' . ($_GET['modalPopUp'] ?? '') ? '?modalPopUp=true' : '');
      } else {
        // $this->tpl->setNotification($result['msg'], 'success');
      }
    }


    $this->tpl->assign('currentModule', $currentModule);
    $this->tpl->assign('moduleId', $moduleId);
    $this->tpl->assign('module', $module);
    $this->tpl->assign('modules', $this->filesRepo->getModules(session('userdata.id')));
    $this->tpl->assign('imgExtensions', ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv', 'webpe']);
    $this->tpl->assign('files', $this->filesRepo->getFilesByModule($module, $moduleId));
  }
}
