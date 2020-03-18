<div class="zaddon-type-container <?= $type->accordion === 'close' ? 'zaddon_closed' : ''?>" data-id="<?= $type->getID() ?>">
	<h3><?= $type->title ?> <button class="zaddon-open" /></h3>
	<?php
	$format = $type->type;
	$name = 'zaddon['.$group->getID().'][' . $type->getID() . ']';
	$template = __DIR__ . '/' . $format . '.php';
	if (file_exists($template)) {
		include $template;
	}
	?>
	<input type="hidden" name="<?= $name ?>[type]" value="<?= $format ?>">
</div>
