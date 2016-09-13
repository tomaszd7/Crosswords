# Crosswords

Crossword generator(PHP)
Works on 8 internal words.

Algorithm: 
- picks words randomly
- tries to fit new word with all placed words starting from the last 
- checks all possible common letters between current 2 words and places with first match
 
KNOWN BUGS: out of grid index when trying to fit word

TO DO (optional): 
- input fields for user words 
- choose best crossword for fe. 10 random generations
- cross edges of word can count as valid (now must be empty)
