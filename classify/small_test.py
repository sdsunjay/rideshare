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

s = set()
for line in lines:
   new_line = line.split()
   for word in new_line:
      s.add(word)
print s
