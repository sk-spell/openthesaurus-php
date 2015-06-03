# originally by Pavel@Janik.cz (Pavel Janík)
# Call as:  awk -f Parse_Thes.awk
# This script requires two input files in the current directory:
#  wordlist.txt: an alphabetically sorted list of all words, 
#                one word per line (Unix line endings)
#  trimthes.txt: an alphabetically sorted list of words and their
#                synonyms, words separated by commas (Unix line endings).
#                Format:
#                  word,synonym1,synonym2,...
#                For example:
#                  automombile,car
#                  car,automombile
#                  cry,cry out,outcry,scream,weep
#                  weep,cry
# 
# It writes two output files, see OUTPUT_DAT and OUTPUT_IDX below.

# This function writes 16bit big-endian word to the output file
function WriteNumber (n) {
  printf "%c%c", (n-(n%256))/256, n%256 >OUTPUT_DAT; BYTES+=2;
}

BEGIN {
  # Input files
  FILE_THESAURUS="trimthes.txt";
  FILE_WORDLIST="wordlist.txt";

  # Output files
  OUTPUT_DAT="../OOo-Thesaurus/th_temp.dat";
  OUTPUT_IDX="../OOo-Thesaurus/th_temp.idx";

  WORDLISTID=0;
  FS=",";

  # Read the wordlist
  while (getline<FILE_WORDLIST) {
    WORDLISTIDS[WORDLISTID]=$0;
    WORDLISTWORDS[$0]=WORDLISTID++;
  }

  # Read the thesaurus database
  while (getline<FILE_THESAURUS) {
    WORD=$1;
    # Do not count words without synonyms
    if (NF==1) continue;
    INDEX[WORD]=BYTES;
    # Write the number of synonyms
    WriteNumber(NF-1);
    # Write synonym indices
    for (i=2; i<=NF; i++) WriteNumber(WORDLISTWORDS[$i]);
  }

  # Write out the index file
  for (i=0; i<WORDLISTID; i++) printf "%s,%d\n", WORDLISTIDS[i], INDEX[WORDLISTIDS[i]] >OUTPUT_IDX;
}
