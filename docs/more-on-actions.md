# More on Actions

Craft's interactions are based around action routes. These have a form similar to:

```twig
action/{{ pluginClass }}/{{ pluginMethod }}?{{ extraParams }}
```

To make your life easier, Shortlist includes helper functions for generating all these action urls, however you are free to build up your own action urls if you need.

For reference, the actions available for Shortlist are listed here. (This can also be a good way to get an idea of what Shortlist is doing under the hood.)

::: tip NOTE:
In these docs we'll be using the default action word trigger of '**action**'. This can be changed per install, so your action word may be different. For safety use the native actionUrl() method to build up your action urls rather than hard-coding.
:::

## GET or POST?

All actions can be triggered by both GET requests (ie. via a link like `/action/..`) or via POST requests (ie. a form post submission). Shortlist accepts both methods throughout except for certain methods which are limited to POST only actions for security. Currently only `list/clearAll` and `list/deleteAll` are limited to POST only actions.

### Building Action Urls for GET Requests
We'll use the native actionUrl() function to build all our action urls:

```twig
<a href="{{ actionUrl('pluginClass/pluginMethod', { optionalParams .. }) }}">Some Action</a>
```

### Setting Action Urls for POST Requests
If you want to trigger actions via a post request simply add a hidden action input with the path to the method in the `value`:

```twig
<input type="hidden" name="action" value="pluginClass/pluginMethod"/>
```

All additional values would be passed as separate inputs.

## Item Actions
All item based actions are on a base route of `shortlist/item`.

### Add an Item
`shortlist/item/add` available as `{{ item.addActionUrl }}`

The main ADD action. Adds this element directly the list from the current context. If none supplied, will add to the default list.

| Parameters |||
| :-- | :-- | :-: |
| `id` | the id for the element to add | <Badge text="required" type="warn" vertical="middle"/> |
| `listId` | the listId to add the item to, if not passed, will use the default list | <Badge text="optional" vertical="middle"/> |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

### Remove an Item
`shortlist/item/remove` available as `{{ item.removeActionUrl }}`

The main REMOVE action. Removes this element directly the list from the current context. If none supplied, will add to the default list.

| Parameters |||
| :-- | :-- | :-: |
| `id` | the id for the element to add | <Badge text="required" type="warn" vertical="middle"/> |
| `listId` | the listId to add the item to, if not passed, will use the default list | <Badge text="optional" vertical="middle"/> |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

### Toggle an Item
`shortlist/item/toggle` available as `{{ item.toggleActionUrl }}`

The main TOGGLE action. This is a bi-directional version of the add/remove action. Will add the item if in the current context list, or remove if it's already in the list. Especially useful for ajax implementations as this will stay consistent across user interaction.

| Parameters |||
| :-- | :-- | :-: |
| `id` | the id for the element to add | <Badge text="required" type="warn" vertical="middle"/> |
| `listId` | the listId to add the item to, if not passed, will use the default list | <Badge text="optional" vertical="middle"/> |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

## List Actions
All list based actions are on a base route of `shortlist/list`.

### Create a New List
`shortlist/list/create` available as `{{ shortlist.newList }}`

| Parameters |||
| :-- | :-- | :-: |
| `title` | the name of the new list, if not passed, will use the default as defined in the settings | <Badge text="optional" vertical="middle"/> |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

### Make a List the Default
`shortlist/list/makeDefault` available as `{{ list.makeDefault }}`

An action to make this list the default for the user. Combine with the `{% if list.default %}..{% endif %}` logic to display as needed. When triggered this will first unset the previously default list.

| Parameters |||
| :-- | :-- | :-: |
| `listId` | the id of the list to make default | <Badge text="required" type="warn" vertical="middle"/> |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

### Clear a List
`shortlist/list/clear` available as `{{ list.clear }}`

An action to CLEAR the current list. This will remove all the items from a list, but leave the actual list.

| Parameters |||
| :-- | :-- | :-: |
| `listId` | the id of the list to clear | <Badge text="required" type="warn" vertical="middle"/> |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

### Delete a List
`shortlist/list/delete` available as `{{ list.delete }}`

An action to DELETE the current list. This will remove the current items from the list and then remove the list itself.

| Parameters |||
| :-- | :-- | :-: |
| `listId` | the id of the list to delete | <Badge text="required" type="warn" vertical="middle"/> |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

### Clear All Lists
`shortlist/list/clearAll`

::: warning NOTE
*requires POST request for security reasons*
:::

| Parameters |||
| :-- | :-- | :-: |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |

### Delete All Lists
`shortlist/list/deleteAll`

::: warning NOTE
*requires POST request for security reasons*
:::

| Parameters |||
| :-- | :-- | :-: |
| `return` | alternative return url - if none supplied will default to the current url | <Badge text="optional" vertical="middle"/> |