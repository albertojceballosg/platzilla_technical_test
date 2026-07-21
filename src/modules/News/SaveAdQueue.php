<?php
	require_once ('Smarty_setup.php');
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/News/lib/AdQueueHelper.class.php');

	$description  = PlatzillaUtils::purify ($_POST, 'quuedescription');
	$period       = PlatzillaUtils::purify ($_POST, 'period');
	$status       = PlatzillaUtils::purify ($_POST, 'status');
	$adQueueName  = PlatzillaUtils::purify ($_POST, 'queuename');
	$record       = PlatzillaUtils::purify ($_POST, 'record', null);

	try {
		if (empty ($adQueueName)) {
			throw new Exception ('El nombre de la cola es requerido!');
		}

		if (empty ($description)) {
			throw new Exception ('La descripción de la cola es requerida!');
		}

		$adQueue = AdQueueHelper::getInstance()->saveAdQueue (
			AdQueue::getInstance ()
				->setId ($record)
				->setDescription($description)
				->setName ($adQueueName)
				->setPeriod ($period)
				->setStatus ($status)
		);

		if (empty ($adQueue)) {
			throw new Exception ('Se ha presentado un error!');
		}

		$_SESSION ['flashmessage'] = array (
			'iserror' => false,
			'message' => (empty ($record)) ? 'Se ha guardado la cola de anuncios' : 'Se ha actualizado la cola de anuncios',
		);
		header ("Location: index.php?module=News&action=EditViewQueues&record={$record}");
	} catch (Exception $e) {
		$_SESSION ['flashmessage'] = array (
			'iserror' => true,
			'message' => $e->getMessage (),
			'data'    => $newsData,
		);
		header ("Location: index.php?module=News&action=EditViewQueues&record={$record}");
	}
	exit ();
