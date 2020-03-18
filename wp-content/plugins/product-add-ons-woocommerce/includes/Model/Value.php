<?php
namespace ZAddons\Model;

use ZAddons\DB;

class Value
{
	private $id;
	private $type_id;

	public $title;
	public $step;
	public $price = 0;
	public $description;
	public $hide;
	public $hide_description;
	public $checked;

	protected $created_at = null;
	protected $created_at_gmt = null;
	protected $updated_at = null;
	protected $updated_at_gmt = null;

	public function __construct($data = null)
	{
		if ($id = filter_var($data, FILTER_VALIDATE_INT)) {
			global $wpdb;
			$prefix = $wpdb->prefix . DB::Prefix;

			$values = $prefix . DB::Values;

			$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM ${values} WHERE id = %d", $id));
		}

		if (is_object($data)) {
			$this->id = intval($data->id);
			$this->type_id = intval($data->type_id);

			$this->title = strval($data->title);
			$this->step = intval($data->step);
			$this->price = floatval($data->price);
			$this->description = strval($data->description);
			$this->hide = boolval($data->hide);
			$this->hide_description = boolval($data->hide_description);
			$this->checked = boolval($data->checked);

			$this->created_at = strtotime($data->created_at);
			$this->created_at_gmt = strtotime($data->created_at_gmt);
			$this->updated_at = strtotime($data->updated_at);
			$this->updated_at_gmt = strtotime($data->updated_at_gmt);
		}
	}

	public function setTypeID($type_id)
	{
		if ($this->type_id) {
			throw new \Exception("Type Id already applied");
		}

		$this->type_id = $type_id;
	}

	public function getTypeID()
	{
		return $this->type_id;
	}

	public function getData()
	{
		$data = [
			'id' => $this->id,
			'title' => $this->title,
			'step' => $this->step,
			'price' => $this->price,
			'description' => $this->description,
			'hide' => $this->hide,
			'hide_description' => $this->hide_description,
			'checked' => $this->checked,
		];

		return $data;
	}

	public function getID()
	{
		return $this->id;
	}

	public static function getByID($id)
	{
		return new self($id);
	}

	public static function getByTypeID($typeID)
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Values;

		$data = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM ${table} WHERE type_id = %d", $typeID)
		);

		$data = array_map(function ($el) {
			return new self($el);
		}, $data);

		return self::formatResults($data);
	}

	public static function formatResults($results)
	{
		$ids = array_map(function (self $result) {
			return $result->getID();
		}, $results);

		return array_combine($ids, $results);
	}

	public function delete()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Values;

		if ($this->id) {
			$wpdb->delete($table, ['id' => $this->id], ['%d']);
		}
		
		$this->id = null;

		return null;
	}

	public function save()
	{
		global $wpdb;
		$prefix = $wpdb->prefix . DB::Prefix;

		$table = $prefix . DB::Values;

		if ($this->id) {
			$wpdb->update(
				$table,
				[
					'title' => $this->title,
					'description' => $this->description,
					'hide' => intval($this->hide),
					'hide_description' => intval($this->hide_description),
					'checked' => intval($this->checked),
					'step' => $this->step,
					'price' => $this->price,
					'updated_at' => current_time('mysql'),
					'updated_at_gmt' => current_time('mysql', 1),
				],
				['id' => $this->id],
				['%s', '%s', '%d', '%d', '%d', '%d', '%f', '%s', '%s'],
				['%d']
			);
		} else {
			if (!$this->type_id) {
				throw new \Exception("Type Id empty");
			}
			$wpdb->insert(
				$table,
				[
					'title' => $this->title,
					'description' => $this->description,
                    'hide' => intval($this->hide),
                    'hide_description' => intval($this->hide_description),
					'checked' => intval($this->checked),
					'step' => $this->step,
					'price' => $this->price,
					'type_id' => $this->type_id,
					'created_at' => current_time('mysql'),
					'created_at_gmt' => current_time('mysql', 1),
					'updated_at' => current_time('mysql'),
					'updated_at_gmt' => current_time('mysql', 1),
				],
				['%s', '%s', '%d', '%d', '%d', '%d', '%f', '%d', '%s', '%s', '%s', '%s']
			);
			$this->id = $wpdb->insert_id;
		}
	}
}
