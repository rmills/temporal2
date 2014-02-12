jQuery.validator.addMethod("cleanurl", function(value, element) {
	return this.optional(element) || /^[a-z0-9_]+$/i.test(value);
}, "Letters, numbers or underscores only please");