<?php foreach ($type->values as $id => $value) {
    if ($value->hide) {
        continue;
    }?>
	<div class="zaddon_radio">
		<label>
			<input
				type="radio"
				<?= $value->checked ? "checked" : "" ?>
				<?php if ($id === 0 && $type->required) { ?>required<?php } ?>
				name="<?= $name ?>[value]"
				value="<?= $value->getID() ?>"
				data-price="<?= $value->price ?>"
				data-type="<?= $type->type ?>"
			/>
			<?= $value->title ?>
			<?= $value->price ? '(' . wc_price($value->price) . ')' : "" ?>
            <?= !$value->hide_description ? '<p class="zaddon-option-description">' . $value->description . '</p>': "" ?>
        </label>
	</div>
<?php } ?>
