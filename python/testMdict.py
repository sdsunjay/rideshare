
from mdict import createHashmap, removeCities
from configPy import configPy
import MySQLdb as mdb
def testMdict():
   con = mdb.connect(configPy.HOST,configPy.USERNAME,configPy.PASSWORD,configPy.DATABASE_NAME)
   strings = ["seeking fremont to irvine saturday 08/22 on when ever time please let me know users i am very exited here to share ride with you","offering slo to woodland hills leaving friday august 7 at 315pm i will be taking the the whole way and can drop you off anywhere along the way 15 gas money per person shoot me a message if you are interested please do not message me asking to leave at a different time or drop you off anywhere else other than the locations listed above the answer is no", "oferringgggg slo to the bay san mateo area tomorrow friday july 24 leaving around 6pm can stop along the way just lemme know returning monday july 27 around 11 am","offering slo to palo alto friday 7/24 at 1pm and palo alto to slo sunday 7/26 undecided time i can drop off anywhere along the way gas money is appreciated","seeking ride for 2 to salinas santa cruz aptos watsonville tomorrow the 17 anytime after 6pm will provide gas money","seeking south ventura county thousand oaks area to slo on sunday will provide gas and company","offering slo to nevada city north on 5 towards sac can drop of anywhere on the way sac stockton roseville etc leaving friday 4/24 after 2pm and returning sunday evening or monday morning early could do either"]
   length = len(strings)
   thousand = "seeking south ventura county thousand oaks area to slo on sunday will provide gas and company"
   with con:
      createHashmap(con)
      removeCities(con,1,thousand)
   #for num in range(0,length):
#	 print strings[num]+"\n"
#	 removeCities(con,num,strings[num])

 #     cur = con.cursor()
      #cur.execute("SELECT * FROM clean_locations_posts")
      #for i in range(cur.rowcount):
#	    row = cur.fetchone()
#	    print row[0]+row[1]+row[2]+row[3]+row[4]+"\n"	    
testMdict()
