if (CCM_EDIT_MODE == false) {
	!function($) {
		$(function() {	
			//add btn class to all button, html input[type="button"], input[type="reset"], input[type="submit"]
			$('input[type=submit]').not(" .btn-default, .btn-primary ,.btn-success ,.btn-info,.btn-warning,.btn-danger,.btn-link").addClass('btn btn-default');
			$('input[type=button]').not(" .btn-default, .btn-primary ,.btn-success ,.btn-info,.btn-warning,.btn-danger,.btn-link").addClass('btn btn-default');
			$('input[type=reset]').not(" .btn-default, .btn-primary ,.btn-success ,.btn-info,.btn-warning,.btn-danger,.btn-link").addClass('btn btn-default');
			$('button').not(" .btn-default, .btn-primary ,.btn-success ,.btn-info,.btn-warning,.btn-danger,.btn-link").addClass('btn btn-default');
			
			//textarea, input[type="text"], input[type="password"], input[type="datetime"], input[type="datetime-local"], input[type="date"], input[type="month"], input[type="time"], input[type="week"], input[type="number"], input[type="email"], input[type="url"], input[type="search"], input[type="tel"], input[type="color"]
			
			$('input[type=text]').not(".form-control").addClass('form-control');
			$('input[type=password]').not(".form-control").addClass('form-control');
			$('input[type=email]').not(".form-control").addClass('form-control');
			$('input[type=url]').not(".form-control").addClass('form-control');
			$('input[type=tel]').not(".form-control").addClass('form-control');
			$('textarea').not(".form-control").addClass('form-control');
			$('select').not(".form-control").addClass('form-control');
		});
	}(window.jQuery)
}
