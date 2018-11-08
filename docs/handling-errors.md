# Handling Errors

Every action a user performs with Shortlist has the (hopefully small) possibility of encountering an error.

The most common type of error you will encounter are validation errors, but other types include permission errors, restriction errors, third party constraints and configuration or implementation issues.

## By Request Type

Depending on the source of the action, the error details will be available from a variety of different sources.

### GET request
Most standard shortlist interactions will be via GET requests. In this case if an action fails the user will be redirected back to the referral url. You can access the errors with the `shortlist.error` variable:

```twig
{% if craft.shortlist.error %}
	<div class="error">
		{{ craft.shortlist.error }}
	</div>
{% endif %}
```

If multiple errors are encountered in the same request, the errors will be returned as list items within a `<ul></ul>`.

### POST request
All the same actions that can be triggered via GET requests can be triggered via POST requests. Check out the full [action reference](more-on-actions.md) for more details on the specific paths you'll need.

When an action is triggered via a POST request we can return the full post request details and attached errors back to the page. In this case you can pull the errors for specific fields as well as the general overall errors. This is especially useful for lists and items that have additional fields assigned, as each field could have a separate error attached.

When you get the posted object back you'll have an array of errors for the overall request, and also specific errors attached to fields in the standard Craft format.

### AJAX request
When actions are triggered via AJAX we return the result as a json array automatically. If the action fails you'll have an array of errors included in the response. These can be mapped directly back to specific elements as however you need.