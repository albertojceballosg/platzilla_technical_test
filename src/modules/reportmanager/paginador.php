<ul class="pagination pull-right">
<?PHP		if ($TOTALPAGES > 1) { ?>
	<?PHP	if ($PAGEACTUAL > 1) { ?>
		<li><a href="?module=emailmanager&action=<?=$_REQUEST['action']?>&parenttab=Settings&isStatus=<?=$isStatus?>&page=1"><i class="fa fa-step-backward"></i></a></li>
		<li><a href="?module=emailmanager&action=<?=$_REQUEST['action']?>&parenttab=Settings&isStatus=<?=$isStatus?>&page=<?=($PAGEACTUAL - 1); ?>"><i class="fa fa-chevron-left"></i></a></li>
	<?PHP	} else {  ?>
		<li class="disabled"><a href="#"><i class="fa fa-step-backward"></i></a></li>
		<li class="disabled"><a href="#"><i class="fa fa-chevron-left"></i></a></li>
	<?PHP	}  ?>
	<?PHP	for($i=1;$i<=$TOTALPAGES;$i++) { ?>
		<?PHP	if ($PAGEACTUAL != $i) { ?>
				<li><a href="?module=emailmanager&action=<?=$_REQUEST['action']?>&parenttab=Settings&isStatus=<?=$isStatus?>&page=<?=$i; ?>"><?=$i?></a></li>
		<?PHP 	} else { ?>
				<li><a href="javascript:void(0)" style="background-color: #eee;"><?=$i?></a></li>
		<?PHP 	} ?>
	<?PHP } ?>
	<?PHP	if ($PAGEACTUAL < $TOTALPAGES) { ?>
		<li><a href="?module=emailmanager&action=<?=$_REQUEST['action']?>&parenttab=Settings&isStatus=<?=$isStatus?>&page=<?=($PAGEACTUAL + 1); ?>"><i class="fa fa-chevron-right"></i></a></li>
		<li><a href="?module=emailmanager&action=<?=$_REQUEST['action']?>&parenttab=Settings&isStatus=<?=$isStatus?>&page=<?=$TOTALPAGES; ?>"><i class="fa fa-step-forward"></i></a></li>
	<?PHP	} else {  ?>
		<li class="disabled"><a href="#"><i class="fa fa-chevron-right"></i></a></li>
		<li class="disabled"><a href="#"><i class="fa fa-step-forward"></i></a></li>
	<?PHP	}  ?>

<?PHP		}  ?>
</ul>

