
import MySQLdb as mdb
hashmapList = []
def helpCreateHashmap(city):
   global hashmapList
   words = city.split()
   
   length = len(words)
   if length > 1:
      for num in range(0,length):
	 if num == length-1:
	    hashmapList[num][words[num]] = 1
	    #print 'lastword is: '+words[num] +' :'+ str(num)
	 else :
	    hashmapList[num][words[num]] = 2
	 #hashmap[words[num]] =
   else:
	 hashmapList[0][city] = 1
def createHashmap():
   global hashmapList
   hashmapA = {'key' : 0}
   hashmapB = {'key' : 2}
   hashmapC = {'key' : 1}
   hashmapList.append(hashmapA)
   hashmapList.append(hashmapB)
   hashmapList.append(hashmapC)
   con = mdb.connect(HOST,USERNAME,PASSWORD,DATABASE_NAME)
   with con:
      cur = con.cursor()
      cur.execute("SELECT city_id,city_name FROM CITIES;")
      location_num = 0
      for i in range(cur.rowcount):
	 
	 row = cur.fetchone()
	 helpCreateHashmap(row[1].lower())



#for x in hashmapList[1]:
#   print x
#testSent = 'I am in oakland'
# testSent1 = "https//www google com maps dir seattle wa san luis obispo ca at 41 3 to 8 5z data 3m1 4b1 4m14 4m13 1m5 1m1 1s0x2c93e550xd59a 2m2 1d to 8 2d47 5 1m5 1m1 1s0x80ece6be7b6cc0xbc0c2ceef0f46 2m2 1d to 6 2d35 4 3e0 will be driving from seattle to slo the weekend before wow week willing to pick anyone up along the way would love some company on this long drive hit me up "
def removeCities(post):
   num = 0
   i = 0
   finalString = ''
   words = post.split()
   for i in range(0,len(words)):
      if words[i] in hashmapList[num]:
#	 print 'Exists!'
         if hashmapList[num][words[i]] == 1:
#	    print 'replaced!'
	    finalString+='LOCATION '
	    num = 0
	 elif hashmapList[num][words[i]] == 2:
#	    print 'replaced!'
	    #words[i] = ''
	    num+=1
      else:
	 finalString+=words[i]+' '
	    #words.pop(i)
   print finalString
createHashmap()
testSent = "offering from las vegas to san francisco departing friday 8/7 between 6 to 7pm message me if interested"
print testSent
removeCities(testSent)
