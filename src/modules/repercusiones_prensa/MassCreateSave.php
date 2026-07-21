<?php
	require_once ('include/utils/PlatzillaUtils.class.php');
	require_once ('modules/repercusiones_prensa/repercusiones_prensa.php');

	global $current_user;

	$attachments = PlatzillaUtils::purify ($_POST, 'attachments', null);
	$dates       = PlatzillaUtils::purify ($_POST, 'date', null);
	$media       = PlatzillaUtils::purify ($_POST, 'media', null);
	$related     = PlatzillaUtils::purify ($_POST, 'related', null);
	$titles      = PlatzillaUtils::purify ($_POST, 'titles', null);
	$urls        = PlatzillaUtils::purify ($_POST, 'urls', null);

	try {
		if (empty ($titles)) {
			throw new Exception ('No se han suministrado los títulos de las publicaciones');
		}
		if (empty ($related)) {
			throw new Exception ('No se han suministrado los valores del campo relacionado con');
		}
		if (empty ($media)) {
			throw new Exception ('No se han suministrado los medios de las publicaciones');
		}
		if (empty ($dates)) {
			throw new Exception ('No se han suministrado las fechas de publicación');
		}
		if (empty ($urls)) {
			throw new Exception ('No se han suministrado los URLs de las publicaciones');
		}
		if (empty ($attachments)) {
			throw new Exception ('No se han suministrado las imágenes de las publicaciones');
		}
		if ((count ($titles) != count ($related)) || (count ($titles) != count ($dates)) || (count ($titles) != count ($media)) || (count ($titles) != count ($urls)) || (count ($titles) != count ($attachments))) {
			throw new Exception ('Las cantidades de títulos, relacionados con, medios, fechas, urls e imágenes no coinciden');
		}

		$additionalFieldNames = array_diff (array_keys ($_POST), array ('action', 'assigntype', 'attachments', 'data', 'date', 'fecha', 'filename', 'media', 'medio_donde_apar', 'module', 'relacionado_con', 'related', 'titles', 'titular', 'urls'));
		$indexes              = array_keys ($_POST ['titles']);
		foreach ($indexes as $index) {
			/** @var repercusiones_prensa|stdClass $rp */
			$rp                                     = new repercusiones_prensa ();
			$rp->column_fields ['medio_donde_apar'] = $media [ $index ];
			$rp->column_fields ['relacionado_con']  = $related [ $index ];
			$rp->column_fields ['fecha']            = $dates [ $index ];
			$rp->column_fields ['titular']          = $titles [ $index ];
			$rp->column_fields ['url']              = $urls [ $index ];

			foreach ($additionalFieldNames as $additionalFieldName) {
				if (isset ($_POST [ $additionalFieldName ][ $index ])) {
					$rp->column_fields [ $additionalFieldName ] = $_POST [ $additionalFieldName ][ $index ];
				}
			}
			$rp->save ('repercusiones_prensa');

			$attachmentKeys = array_keys ($attachments [ $index ]['filename']);
			foreach ($attachmentKeys as $attachmentKey) {
				$fileName = $attachments [ $index ]['filename'][ $attachmentKey ];
				$data     = $attachments [ $index ]['data'][ $attachmentKey ];
				$tempFile = tempnam ('/tmp', 'attachment-');
				file_put_contents ($tempFile, base64_decode (str_replace (' ', '+', substr ($data, strpos ($data, 'base64,') + 7))));

				$_FILES ['filename']['name']     = $fileName;
				$_FILES ['filename']['size']     = filesize ($tempFile);
				$_FILES ['filename']['error']    = 0;
				$_FILES ['filename']['type']     = 'image/jpg';
				$_FILES ['filename']['tmp_name'] = $tempFile;

				/** @var Documents|stdClass $document */
				$document                                     = CRMEntity::getInstance ('Documents');
				$document->column_fields ['notes_title']      = $fileName;
				$document->column_fields ['filename']         = $fileName;
				$document->column_fields ['filesize']         = filesize ($fileName);
				$document->column_fields ['filestatus']       = 1;
				$document->column_fields ['filelocationtype'] = 'I';
				$document->column_fields ['folderid']         = 1;
				$document->column_fields ['assigned_user_id'] = $current_user->id;
				$document->parentid                           = $rp->id;
				$document->save ('Documents');

				unlink ($tempFile);
			}
		}
		header ('Location: index.php?module=repercusiones_prensa&action=index');
	} catch (Exception $e) {
		echo $e->getMessage ();
	}
