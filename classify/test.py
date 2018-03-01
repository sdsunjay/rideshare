from dateutil.parser import parse
from re import search

lines = [
"offering from bayarea san mateo area to slo departing friday 8/7 between 6 to 7pm message me if interested",
"offering sac to slo this saturday august 8 can pick up along the way",
"offering north oc sgv la to slo sunday evening 8/9 around 6 or 7 returning tue 8/11 late afternoon can pickup or dropoff anywhere along the way"
"offering tahoe to slo 8/5/15 will leave around 4 pm from tahoe will take hwy 50 through sac and take int 5 can pick up along the way down asking for for gas ",
"offering west hills calabasas area to slo august 6 thursday morning around 9am",
"offering ride to slo petaluma northbay to slo august 7 8/7 at 6 pm can pick up anywhere along on the way slo to petaluma on sunday august 9 8/9 in the evening can drop off anywhere along my route",
"offering slo to bayarea menlo park / on the way to sfo thursday around 5pm",
"offering carpinteria right by sb up to sj friday night can pick up along the way",
"offering sj to la burbank area friday aug 7 super early am like 4 or 5 la anaheim to sj sunday august 9 2pm",
"offering slo to bayarea thursday afternoon",
"offering nc sd to slo wednesday 8/5 in the evening",
"offering slo to to eastbay oakland alameda thursday around 5pm eastbay to to slo next monday around 10am gas money appreciated",
"offering slo to bayarea august 7"]

weekdays = ["monday", "mon", "tuesday", "tues", "weds", "wednesday", "thursday", "thurs",
"friday", "fri", "saturday", "sat", "sunday", "sun"]
months = ["jan", "feb", "mar", "apr", "may", "june", "july", "aug", "sept", "oct", "nov",
"dec", "january", "february", "march", "april", "august", "september", "october",
"november", "december"]
time_dict = {
    "morning": "10",
    "evening": "5",
    "night": "7",
    "afternoon": "3",
    "early": "8"
}

def find_date():
    for line in lines:
        result = ""
        weekday = ""
        date = ""
        time = ""
        hour = ""
        month = ""
        day = ""

        for word in line.split(" "):
            if month != "" and day == "" and word[0].isdigit():
                day = word
            elif weekday == "" and word in weekdays:
                weekday = word
            elif month == "" and word in months:
                month = word
            elif date == "" and "/" in word and has_numbers(word):
                date = word
            elif time == "" and has_numbers(word):
                non_digit = search("^[0-9]", word)
                if non_digit:
                    time = word[0: non_digit.start() + 1]
                else:
                    time = word
            elif word in time_dict:
	       time = time_dict[word]

            if hour == "" and (has_numbers(word) and "am" in word or word == "am"):
                hour = "am"
            elif hour == "" and (has_numbers(word) and "pm" in word or word == "pm"):
                hour = "pm" 

        if time != "" and hour == "":
            if time >= 12 or time < 8:
                hour = "pm"
            else:
                hour = "am"
        
        print(line + ":")
        if date:
            print("DATE: " + date + " " + time + hour)
            print(parse(date + " " + time + hour))
        elif month and day:
            if time == "":
                time = "12"
                hour = "pm"
            print("MONTH: " + month + " " + day + " " + time + hour)
            print(parse(month + " " + day + " " + time + hour))
        else:
            print("WEEKDAY: " + weekday + " " + time + hour)
            print(parse(date + " " + weekday + " " + time + hour))
        print("")

def has_numbers(str):
    return any(char.isdigit() for char in str)


def main():
    find_date()

if __name__ == "__main__":
    main()
