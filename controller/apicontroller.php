<?php

/**
 * @author hosein parsa <hosin.parsa@yahoo.com>
 */

namespace OCA\Recognition\Controller;

use OC\AppFramework\Http\Request;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\INavigationManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OCP\AppFramework\Http;
use Symfony\Component\EventDispatcher\GenericEvent;
use OC\AppFramework\Middleware\Security\Exceptions\NotLoggedInException;
use OCP\AppFramework\Http\JSONResponse;


/**
 * Class ApiController
 *
 * @package OCA\Files\Controller
 */
class ApiController extends Controller
{
    /** @var string */
    protected $appName;
    /** @var IRequest */
    protected $request;
    /** @var IURLGenerator */
    protected $urlGenerator;
    /** @var INavigationManager */
    protected $navigationManager;
    /** @var IConfig */
    protected $config;
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var IUserSession */
    protected $userSession;
    /** @var IAppManager */
    protected $appManager;
    /** @var \OCP\Files\Folder */
    protected $rootFolder;

    /** @var \OCP\ITagManager */
    private $tagManager;

    /** local file path */
    private $path;

    public $company_id;

    /**
     * @param string $appName
     * @param IRequest $request
     * @param IURLGenerator $urlGenerator
     * @param IL10N $l10n
     * @param IConfig $config
     * @param EventDispatcherInterface $eventDispatcherInterface
     * @param IUserSession $userSession
     * @param IAppManager $appManager
     * @param Folder $rootFolder
     */
    public function __construct($appName,
                                IRequest $request,
                                \OCP\ITagManager $tagManager,
                                IURLGenerator $urlGenerator,
                                IConfig $config,
                                EventDispatcherInterface $eventDispatcherInterface,
                                IUserSession $userSession,
                                IAppManager $appManager,
                                Folder $rootFolder
    )
    {
        parent::__construct($appName, $request);
        $this->appName = $appName;
        $this->request = $request;
        $this->tagManager = $tagManager;
        $this->urlGenerator = $urlGenerator;
        $this->config = $config;
        $this->eventDispatcher = $eventDispatcherInterface;
        $this->userSession = $userSession;
        $this->appManager = $appManager;
        $this->rootFolder = $rootFolder;
        //$company_id = $this->getAppValue();
    }

    /**
     * FIXME: Replace with non static code
     *
     * @return array
     * @throws \OCP\Files\NotFoundException
     */
    public function getStorageInfo()
    {
        $dirInfo = \OC\Files\Filesystem::getFileInfo('/', false);
        return \OC_Helper::getStorageInfo('/', $dirInfo);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return TemplateResponse
     */
    public function index()
    {
        $templateName = 'index';  // will use templates/main.php
        $parameters = ['Token' => $this->getAppValueToken(),
            'Object' => $this->getAppValueObjectDetect(),
            'FaceDetectionAccuracy' => $this->getAppValue_Face_Detection_Accuracy(),
            'MultiFacesStatus' => $this->getAppValue_Multi_Faces_Status(),
            'GrowthStatus' => $this->getAppValue_Growth_Status(),
            'ServerApi' => $this->getAppValueSerevrApi()
        ];
        //die(var_dump($parameters));
        return new TemplateResponse($this->appName, $templateName, $parameters);
    }

    // get app vlaue
    public function getAppValueToken()
    {
        $Token = $this->config->getAppValue($this->appName, 'MANA_Token');
        return $Token;
    }

    public function getAppValueObjectDetect()
    {
        $Object = $this->config->getAppValue($this->appName, 'Object_Detect');
        return $Object;
    }

    public function getAppValue_Face_Detection_Accuracy()
    {
        $Object = $this->config->getAppValue($this->appName, 'Face_Detection_Accuracy');
        return $Object;
    }

    public function getAppValue_Multi_Faces_Status()
    {
        $Object = $this->config->getAppValue($this->appName, 'Multi_Faces_Status');
        return $Object;
    }

    public function getAppValue_Growth_Status()
    {
        $Object = $this->config->getAppValue($this->appName, 'Allow_Growth_Status');
        return $Object;
    }

    public function getAppValueSerevrApi()
    {
        $ServerApi = $this->config->getAppValue($this->appName, 'Server_Api');
        return $ServerApi;
    }

    // set app value
    public function setAppValueToken($Token)
    {
        $this->config->setAppValue($this->appName, 'MANA_Token', $Token);
    }

    public function setAppValue_Growth_Status($Status)
    {
        $this->config->setAppValue($this->appName, 'Allow_Growth_Status', $Status);
    }

    public function setAppValue_Face_Detection_Accuracy($Accuracy)
    {
        $this->config->setAppValue($this->appName, 'Face_Detection_Accuracy', $Accuracy);
    }

    public function setAppValue_Multi_Faces_Status($Multi)
    {
        $this->config->setAppValue($this->appName, 'Multi_Faces_Status', $Multi);
    }

    public function setAppValueObjectDetect($Object)
    {
        $this->config->setAppValue($this->appName, 'Object_Detect', $Object);
    }

    public function setAppValueServerApi($Api)
    {
        $this->config->setAppValue($this->appName, 'Server_Api', $Api);
    }

    //remove app value
    public function removeAppValue()
    {
        $this->config->deleteAppValue($this->appName, 'Object_Detect');
        $this->config->deleteAppValue($this->appName, 'MANA_Token');
        $this->config->deleteAppValue($this->appName, 'Allow_Growth_Status');
        $this->config->deleteAppValue($this->appName, 'Face_Detection_Accuracy');
        $this->config->deleteAppValue($this->appName, 'Multi_Faces_Status');
        $this->config->deleteAppValue($this->appName, 'Server_Api');
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param string $MANA_Token
     * @param string $Object
     */
    public function SaveSettings($MANA_Token, $Object,
                                 $ServerApi, $Accuracy,
                                 $MultiFaces, $GrowthStatus)
    {
        $this->removeAppValue();
        if ($Object === true) {
            $Object = 'True';
        } else {
            $Object = 'False';
        }
        if ($MultiFaces === true) {
            $MultiFaces = 'True';
        } else {
            $MultiFaces = 'False';
        }
        if ($GrowthStatus === true) {
            $GrowthStatus = 'True';
        } else {
            $GrowthStatus = 'False';
        }
        $this->setAppValueToken($MANA_Token);
        $this->setAppValueObjectDetect($Object);
        $this->setAppValueServerApi($ServerApi);
        $this->setAppValue_Growth_Status($GrowthStatus);
        $this->setAppValue_Face_Detection_Accuracy($Accuracy);
        $this->setAppValue_Multi_Faces_Status($MultiFaces);

        $result = [
            'Status' => true,
            'Code' => 200,
            'msg' => 'Data was successfully Saved.'
        ];
        return new JSONResponse($result);
    }


    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param string $directory
     * @param string $files
     * @param string $permissions
     * @param string datadir
     */
    public function AddEmp($directory, $files, $permissions, $datadir)
    {
        $this->path = '/var/www' . $datadir;
        return $this->SendEmp($directory, $files);
    }


    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param string $directory
     * @param string $files
     * @param string $permissions
     * @param string datadir
     */
    public function RecFile($directory, $files, $permissions, $datadir)
    {
        $this->path = '/var/www' . $datadir;
        return $this->SendRec($directory, $files);
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param string $directory
     * @param string $files
     */
    public function SendEmp($directory, $files)
    {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new NotLoggedInException();
        }
        foreach ($files as $file) {
            $dir = $directory . '/' . $file['name'];
            $fileId = $this->rootFolder->getUserFolder($user->getUID())->get($dir)->getId();
            $path = $this->GetFile($fileId);
            $Id[] = $file['id'];
            $new_employee[] = (array)json_decode($this->SendDataEmp($path['file'], $path['name']));
        }
        $result = [
            'status' => 'data sent',
            'id' => $Id,
            'status_code' => 200,
            'data' => $new_employee
        ];
        return new JSONResponse($result);
    }


    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     * @param string $directory
     * @param string $files
     */
    public function SendRec($directory, $files)
    {
        $user = $this->userSession->getUser();
        if ($user === null) {
            throw new NotLoggedInException();
        }
        foreach ($files as $file) {
            $dir = $directory . '/' . $file['name'];
            $fileId = $this->rootFolder->getUserFolder($user->getUID())->get($dir)->getId();
            $path = $this->GetFile($fileId);
            $Id[] = $file['id'];
            $Recognition[] = (array)json_decode($this->Recognition($path['file']));
        }
        $result = ['status' => 'data sended',
            'id' => $Id,
            'status_code' => 200,
            'data' => $Recognition];
        return new JSONResponse($result);
    }

    /**
     * Redirects to the file list and highlight the given file id
     *
     * @param string $fileId file id to show
     * @return RedirectResponse redirect response or not found response
     * @throws \OCP\Files\NotFoundException
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function GetFile($fileId, $details = null)
    {
        $uid = $this->userSession->getUser()->getUID();
        $baseFolder = $this->rootFolder->get($uid . '/files/');
        '@phan-var \OCP\Files\Folder $baseFolder';
        $files = $baseFolder->getById($fileId);
        $params = [];
        if (empty($files)) {
            // probe apps to see if the file is in a different state and can be accessed
            // through another URL
            $event = new GenericEvent(null, [
                'fileid' => $fileId,
                'uid' => $uid,
                'resolvedWebLink' => null,
                'resolvedDavLink' => null,
            ]);
            $this->eventDispatcher->dispatch('files.resolvePrivateLink', $event);

            $webUrl = $event->getArgument('resolvedWebLink');
            $webdavUrl = $event->getArgument('resolvedDavLink');
        } else {
            $file = \current($files);
            if ($file instanceof Folder) {
                // set the full path to enter the folder
                $params['dir'] = $baseFolder->getRelativePath($file->getPath());
            } else {
                // set parent path as dir
                $params['file'] = $this->path . $file->getPath();
                $params['dir'] = $baseFolder->getRelativePath($file->getParent()->getPath());
                // and scroll to the entry
                $filename = explode(".", $file->getName());
                $params['name'] = $filename[0];
            }
            if ($details !== null) {
                $params['details'] = $details;
            }
        }
        if ($params) {
            return $params;
        }
        if ($this->userSession->isLoggedIn() and empty($files)) {
            $param["error"] = $this->l10n->t("You don't have permissions to access this file/folder - Please contact the owner to share it with you.");
            $response = new TemplateResponse("core", 'error', ["errors" => [$param]], 'guest');
            $response->setStatus(Http::STATUS_NOT_FOUND);
            return $response;
        }

        // FIXME: potentially dead code as the user is normally always logged in non-public routes
        throw new \OCP\Files\NotFoundException();
    }

    public function SendDataEmp($photo, $name)
    {
        $curl = curl_init();
        $Token = $this->getAppValueToken();
        $Server = $this->getAppValueSerevrApi();
        $file = new \CURLFile($photo);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $Server . '/attending/new_employee/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('company_id' => $Token, 'employee_id' => '0',
                'employee_photo' => $file,
                'company_name' => 'parsa', 'employee_name' => $name),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function Recognition($photo)
    {
        $curl = curl_init();
        $Token = $this->getAppValueToken();
        $Object = $this->getAppValueObjectDetect();
        $Server = $this->getAppValueSerevrApi();
        $Multi_Face = $this->getAppValue_Multi_Faces_Status();
        $Face_Accuracy = $this->getAppValue_Face_Detection_Accuracy();
        $Growth_Status = $this->getAppValue_Growth_Status();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $Server . '/attending/employee/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('employee_photo' => new \CURLFILE($photo), 'company_id' => $Token,
                'object_detection_status' => $Object,
                'allow_growth_status' => $Growth_Status,
                'face_detection_accuracy' => $Face_Accuracy,
                'multi_faces_status' => $Multi_Face),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
