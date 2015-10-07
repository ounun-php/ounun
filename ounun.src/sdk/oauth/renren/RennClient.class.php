<?php
/** 命名空间 */
namespace sdk\oauth\renren;

class RennClient extends RennClientBase
{
    private $locationService;
    private $albumService;
    private $blogService;
    private $vipinfoService;
    private $evaluationService;
    private $shareService;
    private $pageService;
    private $ubbService;
    private $notificationService;
    private $feedService;
    private $placeService;
    private $profileService;
    private $appService;
    private $statusService;
    private $likeService;
    private $photoService;
    private $checkinService;
    private $commentService;
    private $userService;
    private $friendService;

    function getLocationService()
    {
        if (empty ($this -> locationService ))
        {
            $this->locationService = new \sdk\oauth\renren\service\LocationService ( $this, $this->accessToken );
        }
        return $this->locationService;
    }
    function getAlbumService()
    {
        if (empty ($this -> albumService ))
        {
            $this->albumService = new \sdk\oauth\renren\service\AlbumService ( $this, $this->accessToken );
        }
        return $this->albumService;
    }
    function getBlogService()
    {
        if (empty ($this -> blogService ))
        {
            $this->blogService = new \sdk\oauth\renren\service\BlogService ( $this, $this->accessToken );
        }
        return $this->blogService;
    }
    function getVipinfoService()
    {
        if (empty ($this -> vipinfoService ))
        {
            $this->vipinfoService = new \sdk\oauth\renren\service\VipinfoService ( $this, $this->accessToken );
        }
        return $this->vipinfoService;
    }
    function getEvaluationService()
    {
        if (empty ($this -> evaluationService ))
        {
            $this->evaluationService = new \sdk\oauth\renren\service\EvaluationService ( $this, $this->accessToken );
        }
        return $this->evaluationService;
    }
    function getShareService()
    {
        if (empty ($this -> shareService ))
        {
            $this->shareService = new \sdk\oauth\renren\service\ShareService ( $this, $this->accessToken );
        }
        return $this->shareService;
    }
    function getPageService()
    {
        if (empty ($this -> pageService ))
        {
            $this->pageService = new \sdk\oauth\renren\service\PageService ( $this, $this->accessToken );
        }
        return $this->pageService;
    }
    function getUbbService()
    {
        if (empty ($this -> ubbService ))
        {
            $this->ubbService = new \sdk\oauth\renren\service\UbbService ( $this, $this->accessToken );
        }
        return $this->ubbService;
    }
    function getNotificationService()
    {
        if (empty ($this -> notificationService ))
        {
            $this->notificationService = new \sdk\oauth\renren\service\NotificationService ( $this, $this->accessToken );
        }
        return $this->notificationService;
    }
    function getFeedService()
    {
        if (empty ($this -> feedService ))
        {
            $this->feedService = new \sdk\oauth\renren\service\FeedService ( $this, $this->accessToken );
        }
        return $this->feedService;
    }
    function getPlaceService()
    {
        if (empty ($this -> placeService ))
        {
            $this->placeService = new \sdk\oauth\renren\service\PlaceService ( $this, $this->accessToken );
        }
        return $this->placeService;
    }
    function getProfileService()
    {
        if (empty ($this -> profileService ))
        {
            $this->profileService = new \sdk\oauth\renren\service\ProfileService ( $this, $this->accessToken );
        }
        return $this->profileService;
    }
    function getAppService()
    {
        if (empty ($this -> appService ))
        {
            $this->appService = new \sdk\oauth\renren\service\AppService ( $this, $this->accessToken );
        }
        return $this->appService;
    }
    function getStatusService()
    {
        if (empty ($this -> statusService ))
        {
            $this->statusService = new \sdk\oauth\renren\service\StatusService ( $this, $this->accessToken );
        }
        return $this->statusService;
    }
    function getLikeService()
    {
        if (empty ($this -> likeService ))
        {
            $this->likeService = new \sdk\oauth\renren\service\LikeService ( $this, $this->accessToken );
        }
        return $this->likeService;
    }
    function getPhotoService()
    {
        if (empty ($this -> photoService ))
        {
            $this->photoService = new \sdk\oauth\renren\service\PhotoService ( $this, $this->accessToken );
        }
        return $this->photoService;
    }
    function getCheckinService()
    {
        if (empty ($this -> checkinService ))
        {
            $this->checkinService = new \sdk\oauth\renren\service\CheckinService ( $this, $this->accessToken );
        }
        return $this->checkinService;
    }
    function getCommentService()
    {
        if (empty ($this -> commentService ))
        {
            $this->commentService = new \sdk\oauth\renren\service\CommentService ( $this, $this->accessToken );
        }
        return $this->commentService;
    }
    public function getUserService()
    {
        if (empty ($this -> userService ))
        {
            $this->userService = new \sdk\oauth\renren\service\UserService ( $this, $this->accessToken );
        }
        return $this->userService;
    }
    function getFriendService()
    {
        if (empty ($this -> friendService ))
        {
            $this->friendService = new \sdk\oauth\renren\service\FriendService ( $this, $this->accessToken );
        }
        return $this->friendService;
    }
}
?>
