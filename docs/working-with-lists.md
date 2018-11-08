# Working with Lists

Now that we've covered items, we should look at the second core concept in Shortlist - lists.

Lists are just as simple as items - they're simply a list of items. (Pretty imaginative huh?)

## Getting Lists

First let's setup a way to view the current user's lists. We can get a user's lists using the `shortlist.lists` method like this:

```twig
{% set lists = craft.shortlist.lists %}
```

Simple right? That lists variable is an array of the lists for the current user (be they a logged in member or guest). You'd loop over this array as you would any other array, just be aware - the array could be empty if the user currently has no lists.

## Displaying Lists

Now we have their lists, we also have access to the items in those lists, which is a sub-array within each list. Again - be aware that each list could be empty. Let's loop through them and output them on the page:

```twig
{% set lists = craft.shortlist.lists %}

{% if lists is empty %}
	You have no lists at the moment.
{% else %}
	{% for list in lists %}
		<h3>{{ list.title }}</h3>

		{% if list.items is empty %}
			No items in this list
		{% else %}
			<ul>
				{% for item in list.items %}
					<li>{{ item.title }} (<a href="{{ item.removeActionUrl }}">Remove</a>)</li>
				{% endfor %}
			</ul>
		{% endif %}
		
	{% endfor %}
{% endif %}
```

## Default Lists

The only other key thing to get a grasp of is default lists. Each user has a single default list that, unless otherwise specified, will be shown and acted on by the item add/remove actions.

You can check if the current list is the default list by using the `list.default` variable. Also, we can let a user change their default list using the `list.makeDefaultActionUrl` action:

```twig{9-13}
{% set lists = craft.shortlist.lists %}

{% if lists is empty %}
	You have no lists at the moment.
{% else %}
	{% for list in lists %}
		<h3>{{ list.title }}</h3>
	
		{% if list.default %}
			<em>Default List</em>
		{% else %}
			<a href="{{ list.makeDefaultActionUrl }}">Make Default</a>
		{% endif %}

	{% endfor %}
{% endif %}
```

## Creating New Lists

We can create a new list by using the `shortlist.newListActionUrl` function:

```twig
<a href="{{ shortlist.newListActionUrl }}">Create New List</a>
```

## Clearing Lists

We can clear a single list by using the `list.clearActionUrl` action:

```twig{10}
{% set lists = craft.shortlist.lists %}

{% if lists is empty %}
	You have no lists at the moment.
{% else %}
	{% for list in lists %}
		<h3>{{ list.title }}</h3>
	
		{% if list.items is not empty %}
			<a href="{{ list.clearActionUrl }}">Clear List</a>
		{% endif %}

	{% endfor %}
{% endif %}
```

We can also clear all the lists at once using a simple form:

```twig{2}
<form method="post" action="">
	<input type="hidden" name="action" value="shortlist/list/clearAll" />
	<input type="submit" value="Clear All Lists" />
</form>
```

::: warning NOTE:
Note: For security reasons the **Clear All** function requires a POST action. More about why [here](#).
:::

## Deleting Lists

We can delete a single list by using the `list.delete` action:

```twig{10}
{% set lists = craft.shortlist.lists %}

{% if lists is empty %}
	You have no lists at the moment.
{% else %}
	{% for list in lists %}
		<h3>{{ list.title }}</h3>
	
		{% if list.items is not empty %}
			<a href="{{ list.delete }}">Delete List</a>
		{% endif %}

	{% endfor %}
{% endif %}
```

We can also delete all the lists at once using a simple form:

```twig{2}
<form method="post" action="">
	<input type="hidden" name="action" value="shortlist/list/deleteAll" />
	<input type="submit" value="Delete All Lists" />
</form>
```

::: warning NOTE:
Note: For security reasons the **Delete All** function requires a POST action. More about why [here](#).
:::


## The List Model

The List model is the primary way you'll interact with a user's lists. This model includes all the functions and variables needed to setup a full multi-list setup, and has nice shortcuts for the sub-items and functions within.

::: tip NOTE:
The List model is its own **ElementType**, so it can have its own content, custom fields, and more. You can integrate it into custom plugins as you would any other ElementType.
:::

### `ListModel.id`
The id for this list.

```twig
{{ list.id }}
```

### `ListModel.default`
A true/false marker to denote if this list is the user's current default list.

```twig
{{ list.default }}
```

### `ListModel.title`
The title for the list. The default list title is controlled in the Shortlist settings, but can be set by the user if you expose the option

```twig
{{ list.title }}
```

### `ListModel.owner`
The UserModel for the owner of the current list. If the owner is a member this will be the appropriate craft member UserModel. If they are a guest, this will be a bare UserModel instead.

```twig
{{ list.owner }}
```

### `ListModel.ownerId`
The id of the list owner.

```twig
{{ list.ownerId }}
```

### `ListModel.ownerType`
The user type for the owner. Will be either 'Member' or 'Guest' depending on if they're logged in or not.

```twig
{{ list.ownerType }}
```

### `ListModel.items`
An array of Item models for the items in this list

```twig
{{ list.items }}
```

### List Actions

In addition to the above functions, the List model also contains several [actions](more-on-actions.md#list-actions).