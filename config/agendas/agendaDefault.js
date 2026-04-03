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
    daysOfWeek: [1, 2, 3, 4, 5, 6],
    startTime: '08:00',
    endTime: '21:20',
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
      startTime: '13:00',
      endTime: '14:00',
      daysOfWeek: [1, 2, 3, 4, 5],
      display: 'background',
      className: 'fc-nonbusiness'
    }, {
      startTime: '18:00',
      endTime: maxTime,
      daysOfWeek: [1, 2, 3, 4, 5],
      display: 'background',
      className: 'fc-nonbusiness'
    }, {
      startTime: '13:00',
      endTime: maxTime,
      daysOfWeek: [6],
      display: 'background',
      className: 'fc-nonbusiness'
    }]
  }
  ]
}
