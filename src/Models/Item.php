<?php

namespace Comproso\Framework\Models;

use Illuminate\Database\Eloquent\Model;

use Comproso\Framework\Traits\ModelTrait;

/**
 *  Item Model class.
 *
 *  This is a laravel Model providing the structure for Comproso Test Items.
 *
 *  @copyright License Copyright (C) 2016-2017 Thiemo Kunze <hallo (at) wangaz (dot) com>.
 *
 *  @license AGPL-3.
 *
 *  @see Laravel framework Eloquent for further information.
 *
 */
class Item extends Model
{
    /**
     *  Define used database table.
     *
     *  @var string $table    name of the database table associated with the Item model.
     *
     *  @see Laravel framework Eloquent.
     *
     */
    protected $table = 'items';

    /**
     *  Whitelisting of properties.
     *
     *  This variable allows to define properties that are more openly accessible.
     *
     *  @var array $guarded   names of whitelisted properties.
     *
     *  @see Laravel framework Eloquent.
     *
     */
    protected $guarded = ['results', 'template', 'cssid', 'cssclass', 'name'];

    /**
     *  Blacklisting for JSON transfer.
     *
     *  @var array $hidden    names of blacklisted properties.
     *
     *  @see Laravel framework Eloquent.
     *
     */
    protected $hidden = ['template', 'validation', 'cssid', 'cssclass'];

    /**
     *  Relation to Test Pages.
     *
     *  An item can be only associated with a single Page.
     *
     *  @return object  related Page
     *
     *  @see Laravel framework Eloquent.
     *
     */
    public function page()
    {
	    return $this->belongsTo($this->PageModel);
    }

    /**
     *  Relation to Test Elements.
     *
     *  An item can be only associated with a single Element. Elements are represented
     *  in an agnostic way.
     *
     *  @return object  related Element
     *
     *  @see Laravel framework Eloquent.
     *
     */
    public function element()
    {
	    return $this->morphTo();
    }

    /**
     *  Relation to Test Results.
     *
     *  Every Result (row) can only be associated with one Item.
     *
     *  @return object  related Results.
     *
     *  @todo verify if this representation is adequate.
     *
     */
    public function results()
    {
	    return $this->belongsTo($this->ResultModel);
    }

    /*
	 *	special Item functionalities
	 *
	 */

	/**
   *  Passing item implementation to Element.
   *
   *  This function passes the implementation of a specific Item to an Element or Item Type.
   *
   *  @param array|null   $data   a data row that includes all available information for a
   *    specific item extracted from a raw Test representation.
   *
   *  @return boolean   fail or success.
   *
   */
	public function implement($data)
	{
		return $this->element()->implement($data);
	}

	/**
   *  Item generation.
   *
   *  To provide a secured layer between a full item representation and a presented Item,
   *  a new (temporary) Item is generated containing only necessary information for
   *  presentation.
   *
   *  @param array|object|null  $cache  provides available cached information of an Item.
   *
   *  @return object $item.
   *
   */
	public function generate($cache)
	{
		$item = new Item;
		$item->results = $this->element->generate($cache);
		$item->name = 'item'.$this->id;
		$item->template = $this->template;
		$item->cssid = $this->cssId;
		$item->cssclass = $this->cssClass;

		return $item;
	}

	/**
   *  Proceeding an item.
   *
   *  Uses the test taker's response to provide data and proceed the Item response or results.
   *  Therefore, the proceeding is passed to the Element/item type.
   *
   *  @param array|object|null  $cache  provides available cached information of an Item.
   *
   *  @return mixed   proceeding results.
   *
   */
	public function proceed($cache = null)
	{
		return $this->element->proceed($cache);
	}

	/**
   *  Element finish hook.
   *
   *  This hook is called if the Item/Element presentation is about to be finished. For
   *  instance, it could be used to clear a cache.
   *
   *  @return void
   *
   */
	public function finish()
	{
		if(method_exists($this->element, 'finish'))
			$this->element->finish();
	}

/*
	// export an item (headline)
	public function export()
	{
		return (method_exists($this->element, 'export')) ? array_map(function ($item) {
			return 'item'.$this->id."_".$item;
		}, $this->element->export()) : null;
	}
*/
}
