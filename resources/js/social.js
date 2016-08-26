/**
 * Social_LoginAccount element index
 */
Craft.Social_LoginAccountElementIndex = Garnish.Base.extend(
{
	init: function()
	{
		Garnish.$doc.ready($.proxy(function() {

			Craft.elementIndex.on('updateElements', function() {
				Craft.elementIndex.$elements.find('.element').removeAttr('data-editable');
			});

		}, this));
	},
});

new Craft.Social_LoginAccountElementIndex();


