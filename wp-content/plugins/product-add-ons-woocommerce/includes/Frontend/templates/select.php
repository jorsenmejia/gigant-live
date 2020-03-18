<div class="zaddon_select">
	<select name="<?= $name ?>[value]" <?= $type->required ?> data-type="<?= $type->type ?>">
		<option value="" disabled selected>Choose an option</option>
		<?php foreach ($type->values as $value) {
            if ($value->hide) {
                continue;
            }?>
			<option
				value="<?= $value->getID() ?>"
				data-price="<?= $value->price ?>"
                title="<?= !$value->hide_description ? $value->description : '' ?>"
				<?= $value->checked ? "selected" : "" ?>
			>
				<?= $value->title ?>
				<?= $value->price ? '(' . wc_price($value->price) . ')' : "" ?>
			</option>
		<?php } ?>
	</select>
</div>
