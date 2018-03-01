#!/usr/bin/python
# -*- coding: utf-8 -*-
#used in stemText()
from mdict import createHashmap
from configPy import ConfigPy
import nltk
from nltk.stem import PorterStemmer
from nltk.tokenize import word_tokenize
# stop words
from nltk.corpus import stopwords
from trie import Trie

#use in getText()
import MySQLdb as mdb

#used in lemmaText()
from nltk.stem import WordNetLemmatizer
# used for trie
import marisa_trie


class myTrieClass          (object):

    def __init__(self):
        #self.con = con
        self.con = mdb.connect(ConfigPy.HOST, ConfigPy.USERNAME, ConfigPy.PASSWORD, ConfigPy.DATABASE_NAME)
        self.trie = None
    def makeTrie(self):
        L = []
        with self.con:
            cur = self.con.cursor()
            cur.execute("SELECT city_name FROM CITIES")
            for i in range(cur.rowcount):
                row = cur.fetchone()
                L.append(row[0].lower())
	    cur.execute("SELECT county_name FROM COUNTIES;")
            for i in range(cur.rowcount):
                row = cur.fetchone()
                L.append(row[0].lower())
	    cur.execute("SELECT state_name FROM STATES;")
            for i in range(cur.rowcount):
                row = cur.fetchone()
                L.append(row[0].lower())
        # Print list
        # print '[%s]' % ', '.join(map(str, L))
        self.trie = marisa_trie.Trie(L)
        # return trie

    def checkTrie(self, word):
        word = unicode(word)
        if word in self.trie:
            # Remove this word
            return 1
        else:
            # Is this word the first word in a multi-word sentence
            if self.trie.has_keys_with_prefix(word):
                # concatenate with a space and next word
                return 2
            else:
                return 3
                # Move on to next word


def lemmaText(text):
    words = word_tokenize(text)

    lemmatizer = WordNetLemmatizer()
    for w in words:
        print (lemmatizer.lemmatize(w))


def helpCheckForCitiesWithTrie(myTrie, words, result, w, i):
    if (result == 1):
        return 'LOCATION' + ' '
    elif (result == 2):
        # print w
        nextWord = words.pop(i + 1)
        temp = w + ' ' + nextWord
        result = myTrie.checkTrie(temp)
        return helpCheckForCitiesWithTrie(myTrie, words, result, w, i + 1)
    else:
        return w + ' '


def checkForCitiesWithTrie(con, sentence):
    print 'Original: ' + sentence
    myTrie = myTrieClass()
    myTrie.makeTrie()
    words = word_tokenize(sentence)
    string = ""
    i = 0
    for w in words:
        result = myTrie.checkTrie(w)
        string += helpCheckForCitiesWithTrie(myTrie, words, result, w, i)
        i += 1
    print 'Cleaned: ' + string


def checkForCities(con, sentence):
    """Check if sentence contains city"""
    cur = con.cursor()
    cur.execute("SELECT id,city FROM CITIES;")
    # keep track of number of locations found as all locations are represented
    # by the same key word
    location_num = 0
    new_post = sentence[1]
    for i in range(cur.rowcount):
        row = cur.fetchone()
        if new_post.find(row[1].lower()) != -1:
            # print "found "+row[1].lower()
            new_post = new_post.replace(row[1].lower(), 'LOCATION_NAME')
            location_num += 1
            print new_post
            insertNewPostIntoLocationTable(con, sentence[0], new_post, row[
                                           0], location_num, Locations.Cities)

    return new_post



def removeCities(con):
    """Get text from clean_posts."""
    #list = ["sgv", "sfv", "sj", "sf", "la", "slo", "sb", "sac", "oc", "lb", "sd", "southbay", "eastbay", "northbay",
    #        "southbayarea", "eastbayarea", "northbayarea", "bayarea", "sfo", "lax", "sjc", "sba", "sbp", "calpoly"]
    #abbrev_set = frozenset(list)
    #all_words = []
    #ps = PorterStemmer()
    #stop_words = set(stopwords.words("english"))
    #$str = str_replace("la",'los angeles',$str);
    #$str = str_replace("slo",'san luis obispo',$str);
    #$str = str_replace("sac",'sacramento',$str);
    #$str = str_replace("sf",'san francisco',$str);
    #$str = str_replace("sj",'san jose',$str);
    #$str = str_replace("sd",'san diego',$str);
    #$str = str_replace("sb",'santa barbara',$str);
    #$str = str_replace("lb",'long beach',$str);

    with con:

        cur = con.cursor()

        #temp = hashmapList(con)
        #cur.execute("SELECT O.post,C.post FROM clean_posts C,posts O")
        cur.execute("SELECT pid,post FROM clean_posts")
        #cur.execute("SELECT post FROM posts")

        for i in range(cur.rowcount):
            row = cur.fetchone()
            helpRemoveCities(con, row[0], row[1])
           # print "Original text: "+row[0]+"\n"+"Cleaned text: "+row[1]
            # pass row to another function which determines which "bucket" to put it in
            # first search for individual city
            # then search for county
            # then search for bay area
            # then search for la
            #str = checkForPlaces(con,row)
         #   str = checkForCities(con,row)
            #words = word_tokenize(str)
            # for w in words:
            # all_words.append(ps.stem(w))
            # if w in _set:
            #	if w not in stop_words:
            #		all_words.append(w)
            # stemText(row[0])
    #all_words = nltk.FreqDist(all_words)
    # print(all_words.most_common(100))
            # lemmaText(row[0])
            # print row[0]

def helpRemoveCities(con,post_id,post):
   num = 0
   i = 0
   finalString = ''
   words = post.split()
   city_num = 0
   temp = -1 
   checkForCitiesWithTrie(con,post)
   #for i in range(0,len(words)):
   #   if words[i] in hashmapList[num]:
#	 print 'Exists!'
	 # print "Original: "+words[i]+"\n"
 #        temp =  hashmapList[num][words[i]]
	 #print str(temp)+"\n"
	 # if not ending word
#	 if temp == 1:
#	    num+=1
	 # if ending word
#	 elif temp >= 1:
#	    city_num+=1
#	    finalString+='LOCATION '
	   # print "Replace city in "+str(city_num)+" location"+"\n"+"String so far is: "+finalString
#	    insertNewPostIntoLocationTable(con,str(post_id),finalString,str(temp),str(city_num),str(Locations.Cities))
#	    num = 0
#      else:
#	 num = 0
#	 finalString+=words[i]+' '
  # print "Final String: "+finalString
#   update clean_locations_posts set post=finalString where pid=post_id
#   cursor = con.cursor()
   #cursor.execute ("""
#	    UPDATE clean_locations_posts
#	       SET post=%s
#		  WHERE pid=%s
#		  """, (finalString, str(post_id)))

def stemText(text):

    ps = PorterStemmer()
    words = word_tokenize(text)
    #all_words = [];
    for w in words:
        all_words.append(ps.stem(w))
    #all_words = nltk.FreqDist(all_words)
    # print(all_words.most_common(10))

def findIdOfCity(location):
   return 1

def checkTrie(con, trie): 
    with con:

        cur = con.cursor()

        #temp = hashmapList(con)
        #cur.execute("SELECT O.post,C.post FROM clean_posts C,posts O")
        cur.execute("SELECT pid,post FROM clean_posts")
        #cur.execute("SELECT post FROM posts")

        for i in range(cur.rowcount):
            row = cur.fetchone()
	    while(location = trie.check(row[1])):
	       # locationID = findIdOfCity(location)
	       new_post = new_post.replace(row[1].lower(), 'LOCATION_NAME')
	       location_num += 1
	       print new_post
	       # insertNewPostIntoLocationTable(con, sentence[0], new_post, row[
		 #                       0], location_num, Locations.Cities)
	       
	 # helpRemoveCities(con, row[0], row[1])

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

def main():
   con = mdb.connect(ConfigPy.HOST,ConfigPy.USERNAME,ConfigPy.PASSWORD,ConfigPy.DATABASE_NAME)
   trie = Trie()
   createTrie(con, trie)
   checkTrie(con, trie)
   #myTrie.makeTrie()
   #removeCities(con)
   # getText()

if __name__ == "__main__":
    main()
