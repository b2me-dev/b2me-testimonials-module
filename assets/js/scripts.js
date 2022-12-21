(function() {

	var testimonials = {
		initialize: function() {
			// Call functions here
			this.customFunction();

			// Always put AOS at the bottom
			AOS.init();
		},
		customFunction: function() {
			// Your code
		},

		// See the pattern? Add more functions from here!
	}

	jQuery(document).ready(function() {
		testimonials.initialize();
	});

}());