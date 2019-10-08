if (!hiddenDays) {
  hiddenDays = [0];
}
if (!minTime) {
  minTime = '08:00:00';
}
if (!maxTime) {
  maxTime = '20:45:00';
}
if (firstDay == undefined) {
  firstDay = moment().day();
}
if (!slotDuration) {
  slotDuration = '00:15:00';
}
if (!slotLabelInterval) {
  slotLabelInterval = '00:30:00';
}
if (!businessHours) {
  businessHours = [{
    dow: [1, 2, 3, 4, 5, 6],
    start: '08:00',
    end: '21:20',
  }];
}
if (!boutonsHeaderCenter) {
  var boutonsHeaderCenter = '';
}

if (!eventTextColor) {
  var eventTextColor = '#fff';
}
if (!eventSources) {
  var eventSources = [{
      url: urlBase + '/agenda/' + selected_calendar + '/ajax/getEvents/'
    },
    {
      events: [{
        start: '13:00',
        end: '14:00',
        dow: [1, 2, 3, 4, 5],
        rendering: 'background',
        className: 'fc-nonbusiness'
      }, {
        start: '13:00',
        end: maxTime,
        dow: [6],
        rendering: 'background',
        className: 'fc-nonbusiness'
      }]
    }
  ]
}
