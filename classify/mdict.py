import MySQLdb as mdb


hashmapList = []
class Locations:
       Places, Cities, Counties, States = range(4)

#class hashmapList:

#def __init__(self,con):
   #con = mdb.connect(configPy.HOST,configPy.USERNAME,configPy.PASSWORD,configPy.DATABASE_NAME)
   # Create the hashmapList
#   createHashmap(con)

def helpCreateHashmap(city_id, city):
   global hashmapList
   words = city.split()
   
   length = len(words)
   # Is the city name more than 1 word?
   if length > 1:
      for num in range(0,length):
	 # Are we at the last word in the multi-word city name
	 if num == length-1:
	    hashmapList[num][words[num]] = city_id
	    #print 'lastword is: '+words[num] +' :'+ str(num)
	 else :
	    hashmapList[num][words[num]] = 1
	 #hashmap[words[num]] =
   else:
	 hashmapList[0][city] = city_id
def createHashmap(con):
   global hashmapList
   
   
   hashmapA = {'key' : 0}
   hashmapB = {'key' : 0}
   hashmapC = {'key' : 0}
   hashmapList.append(hashmapA)
   hashmapList.append(hashmapB)
   hashmapList.append(hashmapC)
   with con:
      cur = con.cursor()
      cur.execute("SELECT city_id,city_name FROM CITIES;")
      location_num = 0
      for i in range(cur.rowcount):
	 
	 row = cur.fetchone()
	 helpCreateHashmap(row[0],row[1].lower())



# a location have been found
def insertNewPostIntoLocationTable(con,post_id,post,place_id,location_num,location):
   query="INSERT post_id,post,place_id INTO clean_post_locations "
   cursor = con.cursor()
   add_employee = ("INSERT INTO clean_locations_posts (pid, post, location_id,location_num,table_id) VALUES (%s, %s, %s, %s, %s)")
   data_employee = (post_id,post,place_id,location_num,location)
#Insert new employee
   cursor.execute(add_employee, data_employee)
		  
#for x in hashmapList[1]:
#   print x
#testSent = 'I am in oakland'
# testSent1 = "https//www google com maps dir seattle wa san luis obispo ca at 41 3 to 8 5z data 3m1 4b1 4m14 4m13 1m5 1m1 1s0x2c93e550xd59a 2m2 1d to 8 2d47 5 1m5 1m1 1s0x80ece6be7b6cc0xbc0c2ceef0f46 2m2 1d to 6 2d35 4 3e0 will be driving from seattle to slo the weekend before wow week willing to pick anyone up along the way would love some company on this long drive hit me up "
