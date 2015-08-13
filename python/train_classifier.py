#!/usr/bin/python
# -*- coding: utf-8 -*-
#used in stemText()
import nltk
from nltk.stem import PorterStemmer
from nltk.tokenize import word_tokenize
#stop words
from nltk.corpus import stopwords

#use in getText()
import MySQLdb as mdb

#used in lemmaText()
from nltk.stem import WordNetLemmatizer
#used for trie
import marisa_trie
class myTrieClass          (object):

   def __init__(self):
      #self.con = con
      self.con = mdb.connect(HOST,USERNAME,PASSWORD,DATABASE_NAME)
      self.trie = None
   def makeTrie(self):
      L = []
      with self.con:
	 cur = self.con.cursor()
	 cur.execute("SELECT city FROM CITIES")
	 for i in range(cur.rowcount):
	    row = cur.fetchone()	
	    L.append(row[0].lower())
      # Print list
      #print '[%s]' % ', '.join(map(str, L))
      self.trie = marisa_trie.Trie(L)
      #return trie 
   def checkTrie(self,word):
      word = unicode(word)
      if word in self.trie:
      	 # Remove this word
	 return 1
      else:
	 # Is this word the first word in a multi-word sentence
	 if self.trie.has_keys_with_prefix(word):
	    #concatenate with a space and next word 
	    return 2 
	 else:
	    return 3
	    # Move on to next word

def lemmaText(text):
	words = word_tokenize(text)

	lemmatizer = WordNetLemmatizer()
	for w in words:
		print (lemmatizer.lemmatize(w))

def helpCheckForCitiesWithTrie(myTrie,words,result,w,i):
      if (result == 1):
	 return 'LOCATION'+' '
      elif (result == 2):
	 #print w
	 nextWord = words.pop(i+1)
	 temp = w + ' '+nextWord
	 result = myTrie.checkTrie(temp)
	 return helpCheckForCitiesWithTrie(myTrie,words,result,w,i+1)
      else:
	 return w + ' '
def checkForCitiesWithTrie(con,sentence):
   print 'Original: '+sentence
   myTrie = myTrieClass()
   myTrie.makeTrie() 
   words = word_tokenize(sentence)
   string = ""
   i = 0
   for w in words:
      result = myTrie.checkTrie(w)
      string+=helpCheckForCitiesWithTrie(myTrie,words,result,w,i)
      i+=1
   print 'Cleaned: '+string

#check if sentence contains city
def checkForCities(con,sentence):
	    cur = con.cursor()
	    cur.execute("SELECT id,city FROM CITIES;")
	    #keep track of number of locations found as all locations are represented by the same key word
	    location_num = 0
	    new_post = sentence[1]
	    for i in range(cur.rowcount):
		row = cur.fetchone()
		if new_post.find(row[1].lower()) != -1:
		  #print "found "+row[1].lower()
		  new_post  = new_post.replace(row[1].lower(),'LOCATION_NAME')
		  location_num+=1
		  print new_post
		  insertNewPostIntoLocationTable(con,sentence[0],new_post,row[0],location_num,Locations.Cities)
      
	    return new_post


# get text from clean_posts
def getText():
	con = mdb.connect(HOST,USERNAME,PASSWORD,DATABASE_NAME)
	list=["sgv","sfv","sj","sf","la","slo","sb","sac","oc","lb","sd","southbay","eastbay","northbay","southbayarea","eastbayarea","northbayarea","bayarea","sfo","lax","sjc","sba","sbp","calpoly"];
        abbrev_set = frozenset(list)
        all_words = []
	ps = PorterStemmer()
	stop_words = set(stopwords.words("english"))
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
	    #cur.execute("SELECT O.post,C.post FROM clean_posts C,posts O")
	    cur.execute("SELECT pid,post FROM clean_posts")
	    #cur.execute("SELECT post FROM posts")

	    for i in range(cur.rowcount):
		row = cur.fetchone()	
		#print "Original text: "+row[0]+"\n"+"Cleaned text: "+row[1]
	        #pass row to another function which determines which "bucket" to put it in
		# first search for individual city
		# then search for county
		# then search for bay area
		# then search for la	
	        #str = checkForPlaces(con,row)
	     #   str = checkForCities(con,row)
	        #checkForCitiesWithTrie(con,row[1])
		#words = word_tokenize(str)
		#for w in words:
			#all_words.append(ps.stem(w))
			#if w == "la"	
			#if w in _set:
		#	if w not in stop_words:
		#		all_words.append(w)
		#stemText(row[0])
	#all_words = nltk.FreqDist(all_words)
	#print(all_words.most_common(100))
		#lemmaText(row[0])
		#print row[0]

def stemText(text):

	ps = PorterStemmer()
	words = word_tokenize(text)
	#all_words = [];
	for w in words:
		all_words.append(ps.stem(w))
	#all_words = nltk.FreqDist(all_words)
	#print(all_words.most_common(10))

def main():
	getText()

main()
