<?php foreach ($type->values as $value) {
    if ($value->hide) {
        continue;
    }?>
	<div class="zaddon_text">
		<label for="zaddon_<?= $value->getID() ?>">
			<?= $value->title ?> <?= $value->price ? '(' . wc_price($value->price) . ')' : "" ?>
		</label>
		<br>
		<input
			type="text"
			<?= isset($value->required) && $value->required ? "required" : "" ?>
			id="zaddon_<?= $value->getID() ?>"
			name="<?= $name ?>[value][<?= $value->getID() ?>]"
			data-price="<?= $value->price ?>"
			data-type="<?= $type->type ?>"
		/>
        <?= !$value->hide_description ? '<p class="zaddon-option-description">' . $value->description . '</p>': "" ?>
    </div>
<?php } ?>
