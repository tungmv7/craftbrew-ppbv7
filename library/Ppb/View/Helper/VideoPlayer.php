<?php

/**
 *
 * PHP Pro Bid $Id$ zS7fTza9reX3nvMTMxfjuXgtK6cTA3r0atiAzktnxzQ=
 *
 * @link        http://www.phpprobid.com
 * @copyright   Copyright (c) 2015 Online Ventures Software & CodeCube SRL
 * @license     http://www.phpprobid.com/license Commercial License
 *
 * @version     7.6
 */
/**
 * video player
 *
 * plays either:
 * - local files, in which case it will render the video tag
 * - remote files (embedded code) in which case it will only return the code to be displayed.
 *
 * TODO: the local player needs to be enhanced in order to play everything.
 * - mov files only play in safari and chrome
 */

namespace Ppb\View\Helper;

use Cube\View\Helper\AbstractHelper,
    Cube\Controller\Front,
    Ppb\Db\Table\Row\ListingMedia,
    Ppb\Service\ListingsMedia as ListingsMediaService;

class VideoPlayer extends AbstractHelper
{

    /**
     *
     * render the video player that will play the video
     *
     * @param string|\Ppb\Db\Table\Row\ListingMedia $media
     *
     * @return string
     */
    public function videoPlayer($media)
    {

        $video = $player = $videoId = null;
        if ($media instanceof ListingMedia) {
            if ($media->getData('type') == ListingsMediaService::TYPE_VIDEO) {
                $video = $media->getData('value');
                $videoId = 'video_' . $media->getData('id');
            }
        }
        else if (is_string($media)) {
            $video = $media;
            $videoId = 'video_' . md5(uniqid(time()));
        }

        $video = $this->getView()->renderHtml($video);

        if (strcmp(strip_tags($video), $video) === 0 && !preg_match('#^http(s)?://(.*)+$#i', $video)) {
            /** @var \Cube\View\Helper\Script $scriptHelper */
            $scriptHelper = $this->getView()->getHelper('script');
            $scriptHelper->addHeaderCode('<link href="//vjs.zencdn.net/5.1.0/video-js.css" rel="stylesheet">')
                ->addBodyCode('<script src="//vjs.zencdn.net/5.1.0/video.js"></script>');

            $baseUrl = Front::getInstance()->getRequest()->getBaseUrl();

            $video = $baseUrl . \Ppb\Utility::URI_DELIMITER
                . \Ppb\Utility::getFolder('uploads') . \Ppb\Utility::URI_DELIMITER
                . $video;

            $player = '
                <video id="' . $videoId . '"
                    class="video-js vjs-default-skin"
                    controls preload="auto" width="640" height="350">
                    <source src="' . $video . '" />
                </video>';
        }
        else {
            $player = $video;
        }

        return $player;
    }

}

