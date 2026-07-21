<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
    require_once ('modules/Settings/lib/HowToHelper.class.php');
	
	global $adb;
	
    $assign    = PlatzillaUtils::purify ($_POST, 'howto_assign');
    $html      = PlatzillaUtils::purify ($_POST, 'howto_html');
    $record    = PlatzillaUtils::purify ($_POST, 'record');
    $status    = PlatzillaUtils::purify ($_POST, 'howto_status');
    $title     = PlatzillaUtils::purify ($_POST, 'howto_title');
    $oldImage  = PlatzillaUtils::purify ($_POST,'howto_image', null);
    $video     = PlatzillaUtils::purify ($_POST, 'howto_video');
    $videoType = PlatzillaUtils::purify ($_POST, 'howto_videotype');
    
	try {
        $image         = HowToHelper::getImageResponse ();
        $image         = (empty($image)) ? $oldImage : $image;
        $howToEntities = HowToHelper::buildHowToEntity ($assign);
        HowToHelper::saveHowTo (
            $adb,
            HowToMaster::getInstance()
                ->setEntity ($howToEntities)
                ->setHtml ($html)
                ->setId (intval ($record))
                ->setImage ($image)
                ->setStatus ($status)
                ->setTitle ($title)
                ->setVideo (trim ($video))
                ->setVideoType ($videoType)
        );
        
		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => 'Se ha guardado con éxito la ayuda HowTo',
		);
		header ('Location: index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings&tab=how_to');
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
		);
		header ("Location: index.php?module=Settings&action=HowToEditView&parenttab=Settings&record={$record}&parenttab=Settings&tab=how_to");
	}
	exit ();

