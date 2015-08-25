/* Chinese initialisation for the jQuery UI date picker plugin. */
/* Written by Cloudream (cloudream@gmail.com). */
jQuery(function($){
    $.datepicker.regional['it-IT'] = {
        closeText: 'Chiuso',
        prevText: '&#x3c;Mese Pre',
        nextText: 'Mese Pro&#x3e;',
        currentText: 'Oggi',
        monthNames: ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
        'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
        monthNamesShort: ['Gen','Feb','Mar','Apr','Mag','Giu',
        'Lug','Ago','Set','Ott','Nov','Dic'],
        dayNames: ['Domenica','Lunedi','Martedì','Mercoledì','Giovedi','Venerdì','Sabato'],
        dayNamesShort: ['Domenica','Lunedi','Martedì','Mercoledì','Giovedi','Venerdì','Sabato'],
        dayNamesMin: ['Domenica','Lunedi','Martedì','Mercoledì','Giovedi','Venerdì','Sabato'],
        dateFormat: 'mm-dd-yy', firstDay: 1,
        isRTL: false};
    $.datepicker.setDefaults($.datepicker.regional['it-IT']);
});
