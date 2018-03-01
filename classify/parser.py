from re import compile, search
def matchDate(string):

   weekdays = ["monday", "mon", "tuesday", "tues", "weds", "wednesday", "thursday", "thurs", "friday", "fri", "saturday", "sat", "sunday", "sun"]
   months = ["jan", "feb", "mar", "apr", "may", "june", "july", "aug", "sept", "oct", "nov", "dec", "january", "february", "march", "april", "august", "september", "october", "november", "december"]
   years = r'((?:19|20)\d\d)'
   pattern = r'(%%s) +(%%s), *%s' % years

   thirties = pattern % (
	"september|sep|april|apr|june|jun|november|nov",
	r'0?[1-9]|[12]\d|30')

   thirtyones = pattern % (
	"january|jan|march|mar|may|july|jul|august|aug|october|oct|december|dec",
	r'0?[1-9]|[12]\d|3[01]')

   fours = '(?:%s)' % '|'.join('%02d' % x for x in range(4, 100, 4))

   feb = r'(February) +(?:%s|%s)' % (
	r'(?:(0?[1-9]|1\d|2[0-8])), *%s' % years, # 1-28 any year
	r'(?:(29), *((?:(?:19|20)%s)|2000))' % fours)  # 29 leap years only

   regex = '|'.join('(?:%s)' % x for x in (thirties, thirtyones, feb))
   #r = compile(result)
   result = search(regex, string)
   if result:
       print result.group(0)

def main():
    matchDate('august 7')
    matchDate('8/7/16')

if __name__ == "__main__":
    main()
