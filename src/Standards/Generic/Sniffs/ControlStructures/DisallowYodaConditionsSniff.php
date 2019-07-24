<?php
/**
 * Ban the use of Yoda conditions.
 *
 * @author    Mponos George <gmponos@gmail.com>
 * @author    Mark Scherer <username@example.com>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

namespace PHP_CodeSniffer\Standards\Generic\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class DisallowYodaConditionsSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return Tokens::$comparisonTokens;

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens         = $phpcsFile->getTokens();
        $previousIndex  = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
        $relevantTokens = [
            T_CLOSE_SHORT_ARRAY,
            T_CLOSE_PARENTHESIS,
            T_TRUE,
            T_FALSE,
            T_NULL,
            T_LNUMBER,
            T_DNUMBER,
            T_CONSTANT_ENCAPSED_STRING,
        ];

        if ($previousIndex === false
            || in_array($tokens[$previousIndex]['code'], $relevantTokens, true) === false
        ) {
            return;
        }

        if ($tokens[$previousIndex]['code'] === T_CLOSE_SHORT_ARRAY) {
            $previousIndex = $tokens[$previousIndex]['bracket_opener'];
        }

        $prevIndex = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($previousIndex - 1), null, true);
        if ($prevIndex === false) {
            return;
        }

        if (in_array($tokens[$prevIndex]['code'], Tokens::$arithmeticTokens, true) === true) {
            return;
        }

        if ($tokens[$prevIndex]['code'] === T_STRING_CONCAT) {
            return;
        }

        // Is it a parenthesis.
        if ($tokens[$previousIndex]['code'] === T_CLOSE_PARENTHESIS) {
            // Check what exists inside the parenthesis.
            $closeParenthesisIndex = $phpcsFile->findPrevious(
                Tokens::$emptyTokens,
                ($tokens[$previousIndex]['parenthesis_opener'] - 1),
                null,
                true
            );

            if ($closeParenthesisIndex === false || $tokens[$closeParenthesisIndex]['code'] !== T_ARRAY) {
                if ($tokens[$closeParenthesisIndex]['code'] === T_STRING) {
                    return;
                }

                // If it is not an array check what is inside.
                $found = $phpcsFile->findPrevious(
                    T_VARIABLE,
                    ($previousIndex - 1),
                    $tokens[$previousIndex]['parenthesis_opener']
                );

                // If a variable exists it is not Yoda.
                if ($found !== false) {
                    return;
                }
            }
        }//end if

        $phpcsFile->addError(
            'Usage of Yoda conditions is not allowed; switch the expression order',
            $stackPtr,
            'Found'
        );

    }//end process()


}//end class