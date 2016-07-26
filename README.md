# dateandtime-image
An image that displays a time of an event and the equivalent time in local timezone. Heavily inspired by timeanddate.com

# Live preview
https://www.pablodons.tk/dateandtimeimage.php

# UI
To specify an even time and it's time you can do it in two ways:
- timestamp and timezone
Not very reliable as it doesn't always work as intended
The query variables:
timestamp - Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
timezone - Timezone specified in region or abbreviation

- date and timezone
You specify the dates with an http url query.
The query variables:
s - Seconds
m - Minutes
h - Hours
a - Meridiem (pm or am)
d - Day of the month
M - Month (Jan, Feb,... Dec)
y - Year
timezone - Timezone specified in region or abbreviation
