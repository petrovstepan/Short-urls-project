$(document).ready(function() {

    $('a.submit').click(function(eventObject) {
        a = event.currentTarget;

        form = $(a).parents('form');
        handleForm(form);
    });


    // Настройки js-елементов materialize

    $('select').material_select();

    $('.button-collapse').sideNav({
        menuWidth: 200, // Default is 300
        edge: 'left', // Choose the horizontal origin
        closeOnClick: false, // Closes side-nav on <a> clicks, useful for Angular/Meteor
        draggable: true, // Choose whether you can drag to open on touch screens,
        onOpen: function(el) {}, // A function to be called when sideNav is opened
      onClose: function(el) {} // A function to be called when sideNav is closed
    }
  );

    $('.datepicker').pickadate({
        selectMonths: true,
        selectYears: 3,
        today: 'Today',
        clear: 'Clear',
        close: 'Ok',
        closeOnSelect: true // Close upon selecting a date,
    });

});

function handleForm(form)
{
    status = true;

    if (form.attr('id') === 'stats')
    {
        handleStats();
    }

    form.submit();
}

function handleStats()
{
    formObj = $('#stats');
    action = formObj.attr('action');

    formArray = $('#stats').serializeArray();

    newaction = action.replace(/\w*$/, formArray[1]['value']);
    formObj.attr('action', newaction);

    $('.select-wrapper').detach();
}

