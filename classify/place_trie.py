from trie import Trie
from configPy import ConfigPy
import MySQLdb as mdb

def createTrie(con, trie): 
   
   # Create the trie and insert some words then do some tests
   with con:
      cur = con.cursor()
      cur.execute("SELECT city_name FROM CITIES;")
      for i in range(cur.rowcount):
	 
	 row = cur.fetchone()
	 trie.insert(row[0].lower())

      cur.execute("SELECT county_name FROM COUNTIES;")
      for i in range(cur.rowcount):
	 
	 row = cur.fetchone()
	 trie.insert(row[0].lower())

      cur.execute("SELECT state_name FROM STATES;")
      for i in range(cur.rowcount):
	 row = cur.fetchone()
	 trie.insert(row[0].lower())
      # print trie.get_all_with_prefix("san")
def checkTrie(con, trie): 
        
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
   "offering"]
   for line in lines:
      print line
      location_num = 0
      new_line = line
      word = trie.check(line)
      
      if word:
	 print 'Success: ' + str(word) 
	 # locationID = findIdOfCity(word)
	 # new_post = line.replace(word, 'LOCATION_NAME')
	 # location_num += 1
	 # print new_post

def main():
   con = mdb.connect(ConfigPy.HOST, ConfigPy.USERNAME, ConfigPy.PASSWORD, ConfigPy.DATABASE_NAME)
   trie = Trie()
   createTrie(con, trie)
   checkTrie(con, trie)

if __name__ == "__main__":
    main()
