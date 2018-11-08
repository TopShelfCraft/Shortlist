# Working with Items

The most basic concept in Shortlist is **items**.

An item can be **any element** within Craft. That means you can use Shortlist with _entries_, _users_, _assets_, _categories_, and _everything else_ that's an element. This includes completely custom third-party `ElementTypes`. Spiffy!

::: tip NOTE:
We'll use _entries_ in all our examples just for simplicity, but the same things will work with **any element type**.
:::

## Getting an Item

Getting an item's information is pretty straightforward. We'll call the `item` method and pass in the element's id:

```twig
{% set item = craft.shortlist.item(entry.id) %}
```

Whenever we're dealing with an individual item we're actually working with a `Shortlist_ItemModel`.

::: v-pre
::: tip NOTE
If the item specified is not in one of the current user's lists, some of the model's variables will return [null](http://php.net/language.types.null), (e.g. `{{ item.listId }}`).
:::


## Adding/Removing Items

Let's start with a loop of entries using the standard Craft tags:

```twig
{% for entry in craft.entries.section('news') %}
	<h2>{{ entry.title }}</h2>
{% endfor %}
```


### From Single Lists

Working with single lists is pretty easy. We'll add tags to check if the current item is in the list and then display add/remove links as appropriate.

```twig
{% for entry in craft.entries.section('news') %}
	{% set item = craft.shortlist.item(entry.id) %}
	<h2>{{ entry.title }}</h2>

	{% if item.inList %}
		{# This item is already in the default list, show a remove button #}
		<a href="{{ item.removeActionUrl }}">Remove from List</a>
	{% else %}
		{# Not currently in the default list, show an add button #}
		<a href="{{ item.addActionUrl }}">Add to List</a>
	{% endif %}
{% endfor %}
```

Besides `{{ item.addAction }}` and `{{ item.removeActionUrl }}`, you can also use a handy `{{ item.toggleActionUrl }}` action. This is a bi-directional version of the add/remove action. Basically, it will add the item if it's not already in the current list or remove if it's already in the list. This action is especially useful for ajax implementations as it will stay consistent across user interaction.

You can learn more about Actions [here](more-on-actions.md#item-actions).


### From Multiple Lists

If the users have multiple lists, perhaps we want to give them details about those other lists too. That's just as simple:

```twig{13-27}
{% for entry in craft.entries.section('news') %}
	{% set item = craft.shortlist.item(entry.id) %}
	<h2>{{ entry.title }}</h2>

	{% if item.inList %}
		{# This item is already in the default list, show a remove button #}
		<a href="{{ item.removeActionUrl }}">Remove from List</a>
	{% else %}
		{# Not currently in the default list, show an add button #}
		<a href="{{ item.addActionUrl }}">Add to List</a>
	{% endif %}

	{# Show details about the other lists for the user #}
	{# First test if they have other lists #}
	{% if item.otherLists is not empty %}
		<h3>Other Lists</h3>
		{% for otherList in item.otherLists %}

			{# Test if this item is in this list and display buttons as appropriate #}
			{% if otherList.inList %}
				<a href="{{ otherList.removeActionUrl }}">Remove from {{ otherList.title }}</a>
			{% else %}
				<a href="{{ otherList.addActionUrl }}">Add to {{ otherList.title }}</a>
			{% endif %}

		{% endfor %}
	{% endif %}
	
{% endfor %}
```


## Total Item Count

You can get the total number of items in all a user's lists by using this simple helper function:

```twig
{% set totalItems = craft.shortlist.itemCount %}

{% if totalItems > 0 %}
	You have {{ totalItems }} items in your lists.
{% else %}
	You have no items yet.
{% endif %}
```

## The Item Model

The Item model is the primary way you'll interact with items when using Shortlist. This model includes functions for all the main item operations, and variables and helpers for all the various state and data actions you'll need.

::: tip NOTE:
The Item model is its own **ElementType**, so it can have its own content, custom fields, and more. You can integrate it into custom plugins as you would any other ElementType.
:::

### `ItemModel.id`
The id for this item. Will be null if this item is a bare item.

```twig
{{ item.id }}
```

### `ItemModel.inList`
A true/false variable to denote if this item is in the user's list. Note: This follows the context of the list being viewed. If not in a specific list view, will refer to the default list context

```twig
{{ item.inList }}
```

### `ItemModel.elementId`
The elementId of the Id related to this item. Eg. '14'

```twig
{{ item.elementId }}
```

### `ItemModel.elementType`
The elementType of the related element. Eg. 'entry' or 'user' etc...

```twig
{{ item.elementType }}
```

### `ItemModel.listId`
The id of the List for this item. May return NULL if the user has no current lists. (A list will be automatically created when an item is first added in this case)

```twig
{{ item.listId }}
```

### `ItemModel.otherLists`
An array of items, all with the current element, but in the context of the other lists for the current user. This array excludes the current default list.

```twig
{{ item.otherLists }}
```

### `ItemModel.lists`
An array of items, all with the current element, but in the context of the all lists for the current user. This array includes the current default list.

```twig
{{ item.lists }}
```

### `ItemModel.title`
The title of the parent element. ie. 'Entry One', useful when used in the context of the List array

```twig
{{ item.title }}
```

### `ItemModel.element`
The element model for the parent element. ie. if this is an entry, this will be the EntryModel. This is exactly the same as if you'd used the normal craft.entries.. tags to retrieve the element. Useful in the context of the List array: 

```twig
{{ item.title }} by {{ item.element.author }}, {{ item.element.someOtherField }}
```

### `ItemModel.parentList`
The List model for the parent list. Will return a List model which can be used to access all the variables and functions on the parent list.

```twig
{{ item.parentList }}
```

### Item Actions

In addition to the above functions, the Item model also contains several [actions](more-on-actions.md#item-actions).
