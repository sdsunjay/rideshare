import random
import re
from nltk.corpus import stopwords
import nltk.classify.util, nltk.metrics
from nltk.classify import NaiveBayesClassifier
from nltk.corpus import stopwords
allWords = []

def cleanText(text):
    """Clean up the text."""
    return re.sub(r'\s+', ' ', re.sub(r'\W+', " ", text.lower())).strip()

def getAllWords(allText):
    """Find all unique words in the text (|allText|). |allText| is a list of strings."""
    global allWords

    words = {}
    # Remove stopwords while we are here
    stopset = set(stopwords.words('english'))
    for text in allText:
        for word in text.split():
            if word not in stopset:
                words[word] = True
    allWords = words.keys()

def getFeatures(words):
    """Given a list of strings (|text|), return a dict of features for that text."""
    global allWords

    features = {}
    # Turn the input into a set of words.
    textSet = set(words.split())
    for word in allWords:
        features["Has: %s" % word] = (word in words)
    return features
    #return dict([(word, True) for word in words])


def splitIntoFolds(lst, n):
    """Split lst into n chunks."""
    division = len(lst) / float(n)
    # return [lst[int(round(division * i)): int(round(division * (i + 1)))] for i in xrange(n)]
    # for python 3+
    return [lst[int(round(division * i)): int(round(division * (i + 1)))] for i in range(n)]

def evaluate(allData, numberOfFolds):
    """For cross-validation, we divide the data (|allData|) into N (|numberOfFolds|) subsets. Then we run each subset."""
    accuracy = 0
    folds = splitIntoFolds(allData, numberOfFolds)
    startIndex = 0
    for i in range(0, numberOfFolds):
        endIndex = startIndex + len(folds[i]) - 1
        training = folds[0:(i + 0)] + folds[(i + 1):]
        mergedTraining = [item for sublist in training for item in sublist]
        test = folds[i]

        runAccuracy = run(test, mergedTraining)
        print("Run %d (%d - %d) : %0.5f" %
              (i, startIndex, endIndex, runAccuracy))
        startIndex = len(folds[i]) + startIndex

        accuracy += runAccuracy

    print("Total Accuracy: %0.5f" % (accuracy / numberOfFolds))

def findInaccuracies(classy, test):
    """Using the dev-test set, we can generate a list of the errors that the classifier makes when predicting if an individual is offering or seeking a ride"""
    errors = []
    for (line, tag) in test:
       guess = classy.classify(getFeatures(line))
       if guess != tag:
          errors.append( (tag, guess, line) )
          for (tag, guess, line) in sorted(errors):
             print("Correct = " + tag + " Guess = " + guess + " line = " + line)
	     # print('correct={:<8} guess={:<8s} name={:<30}'.format(tag, guess, name))

def run(test, train):
    """Convert list of tuples into list of features (first dict of features, then LazyMap of features). Each argument is a list of strings."""
    trainingSet = nltk.classify.apply_features(getFeatures, train)
    testSet = nltk.classify.apply_features(getFeatures, test)
    classy = NaiveBayesClassifier.train(trainingSet)
    # Show the posts which were classified incorrectly.
    # findInaccuracies(classy,test)
    # Show the 100 most informative features
    # classy.show_most_informative_features(100)
    return nltk.classify.accuracy(classy, testSet)

def main():
    """The main function."""
    numberOfFolds = 10
    allData = []

    with open('training_texts/seeking.txt', 'r') as f:
        for line in f:
            allData.append((cleanText(line), 'seeking'))

    with open('training_texts/offering.txt', 'r') as f:
        for line in f:
            allData.append((cleanText(line), 'offering'))

    # Every day I'm shuffling.
    random.shuffle(allData)

    print("Total number of lines in training set: %d" % len(allData))

    getAllWords([instance[0] for instance in allData])
    evaluate(allData, numberOfFolds)

if __name__ == "__main__":
    if os.path.exists('training_texts/seeking.txt') == False:
        print('Training file (seeking.txt) not found in the app path.')
        exit()
    if os.path.exists('training_texts/offering.txt') == False:
        print('Training file (offering.txt) not found in the app path.')
        exit()
    main()
