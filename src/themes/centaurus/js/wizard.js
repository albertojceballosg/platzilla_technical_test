/*
 * Fuel UX Wizard
 * https://github.com/ExactTarget/fuelux
 *
 * Copyright (c) 2012 ExactTarget
 * Licensed under the MIT license.
 */

// WIZARD CONSTRUCTOR AND PROTOTYPE

var Wizard = function (element, options) {
	var kids;

	this.jQueryelement = jQuery(element);
	this.options = jQuery.extend({}, jQuery.fn.wizard.defaults, options);
	this.currentStep = this.options.selectedItem.step;
	this.numSteps = this.jQueryelement.find('.steps li').length;
	this.jQueryprevBtn = this.jQueryelement.find('button.btn-prev');
	this.jQuerynextBtn = this.jQueryelement.find('button.btn-next');

	kids = this.jQuerynextBtn.children().detach();
	this.nextText = jQuery.trim(this.jQuerynextBtn.text());
	this.jQuerynextBtn.append(kids);

	// handle events
	this.jQueryprevBtn.on('click', jQuery.proxy(this.previous, this));
	this.jQuerynextBtn.on('click', jQuery.proxy(this.next, this));
	this.jQueryelement.on('click', 'li.complete', jQuery.proxy(this.stepclicked, this));
	
	if(this.currentStep > 1) {
        this.selectedItem(this.options.selectedItem);
	}
};

Wizard.prototype = {

	constructor: Wizard,

	setState: function () {
		var canMovePrev = (this.currentStep > 1);
		var firstStep = (this.currentStep === 1);
		var lastStep = (this.currentStep === this.numSteps);

		// disable buttons based on current step
		this.jQueryprevBtn.attr('disabled', (firstStep === true || canMovePrev === false));

		// change button text of last step, if specified
		var data = this.jQuerynextBtn.data();
		if (data && data.last) {
			this.lastText = data.last;
			if (typeof this.lastText !== 'undefined') {
				// replace text
				var text = (lastStep !== true) ? this.nextText : this.lastText;
				var kids = this.jQuerynextBtn.children().detach();
				this.jQuerynextBtn.text(text).append(kids);
			}
		}

		// reset classes for all steps
		var jQuerysteps = this.jQueryelement.find('.steps li');
		jQuerysteps.removeClass('active').removeClass('complete');
		jQuerysteps.find('span.badge').removeClass('badge-primary').removeClass('badge-success');

		// set class for all previous steps
		var prevSelector = '.steps li:lt(' + (this.currentStep - 1) + ')';
		var jQueryprevSteps = this.jQueryelement.find(prevSelector);
		jQueryprevSteps.addClass('complete');
		jQueryprevSteps.find('span.badge').addClass('badge-success');

		// set class for current step
		var currentSelector = '.steps li:eq(' + (this.currentStep - 1) + ')';
		var jQuerycurrentStep = this.jQueryelement.find(currentSelector);
		jQuerycurrentStep.addClass('active');
		jQuerycurrentStep.find('span.badge').addClass('badge-primary');

		// set display of target element
		var target = jQuerycurrentStep.data().target;
		this.jQueryelement.find('.step-pane').removeClass('active');
		jQuery(target).addClass('active');

		// reset the wizard position to the left
		jQuery('.wizard .steps').attr('style','margin-left: 0');

		// check if the steps are wider than the container div
		var totalWidth = 0;
		jQuery('.wizard .steps > li').each(function () {
			totalWidth += jQuery(this).outerWidth();
		});
		var containerWidth = 0;
		if (jQuery('.wizard .actions').length) {
			containerWidth = jQuery('.wizard').width() - jQuery('.wizard .actions').outerWidth();
		} else {
			containerWidth = jQuery('.wizard').width();
		}
		if (totalWidth > containerWidth) {
		
			// set the position so that the last step is on the right
			var newMargin = totalWidth - containerWidth;
			jQuery('.wizard .steps').attr('style','margin-left: -' + newMargin + 'px');
			
			// set the position so that the active step is in a good
			// position if it has been moved out of view
			if (jQuery('.wizard li.active').position().left < 200) {
				newMargin += jQuery('.wizard li.active').position().left - 200;
				if (newMargin < 1) {
					jQuery('.wizard .steps').attr('style','margin-left: 0');
				} else {
					jQuery('.wizard .steps').attr('style','margin-left: -' + newMargin + 'px');
				}
			}
		}

		this.jQueryelement.trigger('changed');
	},

	stepclicked: function (e) {
		var li = jQuery(e.currentTarget);

		var index = this.jQueryelement.find('.steps li').index(li);

		var evt = jQuery.Event('stepclick');
		this.jQueryelement.trigger(evt, {step: index + 1});
		if (evt.isDefaultPrevented()) return;

		this.currentStep = (index + 1);
		this.setState();
	},

	previous: function () {
		var canMovePrev = (this.currentStep > 1);
		if (canMovePrev) {
			var e = jQuery.Event('change');
			this.jQueryelement.trigger(e, {step: this.currentStep, direction: 'previous'});
			if (e.isDefaultPrevented()) return;

			this.currentStep -= 1;
			this.setState();
		}
	},

	next: function () {
		var canMoveNext = (this.currentStep + 1 <= this.numSteps);
		var lastStep = (this.currentStep === this.numSteps);

		if (canMoveNext) {
			var e = jQuery.Event('change');
			this.jQueryelement.trigger(e, {step: this.currentStep, direction: 'next'});

			if (e.isDefaultPrevented()) return;

			this.currentStep += 1;
			this.setState();
		}
		else if (lastStep) {
			this.jQueryelement.trigger('finished');
		}
	},

	selectedItem: function (selectedItem) {
		var retVal, step;

		if(selectedItem) {

			step = selectedItem.step || -1;

			if(step >= 1 && step <= this.numSteps) {
				this.currentStep = step;
				this.setState();    
			}

			retVal = this;	
		}
		else {
			retVal = { step: this.currentStep };
		}

		return retVal;
	}
};


// WIZARD PLUGIN DEFINITION

jQuery.fn.wizard = function (option, value) {
	var methodReturn;

	var jQueryset = this.each(function () {
		var jQuerythis = jQuery(this);
		var data = jQuerythis.data('wizard');
		var options = typeof option === 'object' && option;

		if (!data) jQuerythis.data('wizard', (data = new Wizard(this, options)));
		if (typeof option === 'string') methodReturn = data[option](value);
	});

	return (methodReturn === undefined) ? jQueryset : methodReturn;
};

jQuery.fn.wizard.defaults = {
    selectedItem: {step:1}
};

jQuery.fn.wizard.Constructor = Wizard;


// WIZARD DATA-API

jQuery(function () {
	jQuery('body').on('mousedown.wizard.data-api', '.wizard', function () {
		var jQuerythis = jQuery(this);
		if (jQuerythis.data('wizard')) return;
		jQuerythis.wizard(jQuerythis.data());
	});
});

