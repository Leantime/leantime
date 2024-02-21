<?php
/*
 * Copyright 2007 ZXing authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Zxing\Common\Reedsolomon;

/**
 * <p>Implements Reed-Solomon decoding, as the name implies.</p>
 *
 * <p>The algorithm will not be explained here, but the following references were helpful
 * in creating this implementation:</p>
 *
 * <ul>
 * <li>Bruce Maggs.
 * <a href="http://www.cs.cmu.edu/afs/cs.cmu.edu/project/pscico-guyb/realworld/www/rs_decode.ps">
 * "Decoding Reed-Solomon Codes"</a> (see discussion of Forney's Formula)</li>
 * <li>J.I. Hall. <a href="www.mth.msu.edu/~jhall/classes/codenotes/GRS.pdf">
 * "Chapter 5. Generalized Reed-Solomon Codes"</a>
 * (see discussion of Euclidean algorithm)</li>
 * </ul>
 *
 * <p>Much credit is due to William Rucklidge since portions of this code are an indirect
 * port of his C++ Reed-Solomon implementation.</p>
 *
 * @author Sean Owen
 * @author William Rucklidge
 * @author sanfordsquires
 */
final class ReedSolomonDecoder
{
	public function __construct(private $field)
 {
 }

	/**
	 * <p>Decodes given set of received codewords, which include both data and error-correction
	 * codewords. Really, this means it uses Reed-Solomon to detect and correct errors, in-place,
	 * in the input.</p>
	 *
	 * @param data $received and error-correction codewords
	 * @param number     $twoS of error-correction codewords available
	 *
	 * @throws ReedSolomonException if decoding fails for any reason
	 */
	public function decode(&$received, $twoS)
	{
		$poly = new GenericGFPoly($this->field, $received);
		$syndromeCoefficients = fill_array(0, $twoS, 0);
		$noError = true;
		for ($i = 0; $i < $twoS; $i++) {
			$eval = $poly->evaluateAt($this->field->exp($i + $this->field->getGeneratorBase()));
			$syndromeCoefficients[(is_countable($syndromeCoefficients) ? count($syndromeCoefficients) : 0) - 1 - $i] = $eval;
			if ($eval != 0) {
				$noError = false;
			}
		}
		if ($noError) {
			return;
		}
		$syndrome = new GenericGFPoly($this->field, $syndromeCoefficients);
		$sigmaOmega =
			$this->runEuclideanAlgorithm($this->field->buildMonomial($twoS, 1), $syndrome, $twoS);
		$sigma = $sigmaOmega[0];
		$omega = $sigmaOmega[1];
		$errorLocations = $this->findErrorLocations($sigma);
		$errorMagnitudes = $this->findErrorMagnitudes($omega, $errorLocations);
		$errorLocationsCount = is_countable($errorLocations) ? count($errorLocations) : 0;
		for ($i = 0; $i < $errorLocationsCount; $i++) {
			$position = (is_countable($received) ? count($received) : 0) - 1 - $this->field->log($errorLocations[$i]);
			if ($position < 0) {
				throw new ReedSolomonException("Bad error location");
			}
			$received[$position] = GenericGF::addOrSubtract($received[$position], $errorMagnitudes[$i]);
		}
	}

	private function runEuclideanAlgorithm($a, $b, $R)
	{
		// Assume a's degree is >= b's
		if ($a->getDegree() < $b->getDegree()) {
			$temp = $a;
			$a = $b;
			$b = $temp;
		}

		$rLast = $a;
		$r = $b;
		$tLast = $this->field->getZero();
		$t = $this->field->getOne();

		// Run Euclidean algorithm until r's degree is less than R/2
		while ($r->getDegree() >= $R / 2) {
			$rLastLast = $rLast;
			$tLastLast = $tLast;
			$rLast = $r;
			$tLast = $t;

			// Divide rLastLast by rLast, with quotient in q and remainder in r
			if ($rLast->isZero()) {
				// Oops, Euclidean algorithm already terminated?
				throw new ReedSolomonException("r_{i-1} was zero");
			}
			$r = $rLastLast;
			$q = $this->field->getZero();
			$denominatorLeadingTerm = $rLast->getCoefficient($rLast->getDegree());
			$dltInverse = $this->field->inverse($denominatorLeadingTerm);
			while ($r->getDegree() >= $rLast->getDegree() && !$r->isZero()) {
				$degreeDiff = $r->getDegree() - $rLast->getDegree();
				$scale = $this->field->multiply($r->getCoefficient($r->getDegree()), $dltInverse);
				$q = $q->addOrSubtract($this->field->buildMonomial($degreeDiff, $scale));
				$r = $r->addOrSubtract($rLast->multiplyByMonomial($degreeDiff, $scale));
			}

			$t = $q->multiply($tLast)->addOrSubtract($tLastLast);

			if ($r->getDegree() >= $rLast->getDegree()) {
				throw new ReedSolomonException("Division algorithm failed to reduce polynomial?");
			}
		}

		$sigmaTildeAtZero = $t->getCoefficient(0);
		if ($sigmaTildeAtZero == 0) {
			throw new ReedSolomonException("sigmaTilde(0) was zero");
		}

		$inverse = $this->field->inverse($sigmaTildeAtZero);
		$sigma = $t->multiply($inverse);
		$omega = $r->multiply($inverse);

		return [$sigma, $omega];
	}

	private function findErrorLocations($errorLocator)
	{
		// This is a direct application of Chien's search
		$numErrors = $errorLocator->getDegree();
		if ($numErrors == 1) { // shortcut
			return [$errorLocator->getCoefficient(1)];
		}
		$result = fill_array(0, $numErrors, 0);
		$e = 0;
		for ($i = 1; $i < $this->field->getSize() && $e < $numErrors; $i++) {
			if ($errorLocator->evaluateAt($i) == 0) {
				$result[$e] = $this->field->inverse($i);
				$e++;
			}
		}
		if ($e != $numErrors) {
			throw new ReedSolomonException("Error locator degree does not match number of roots");
		}

		return $result;
	}

	private function findErrorMagnitudes($errorEvaluator, $errorLocations)
	{
		// This is directly applying Forney's Formula
		$s = is_countable($errorLocations) ? count($errorLocations) : 0;
		$result = fill_array(0, $s, 0);
		for ($i = 0; $i < $s; $i++) {
			$xiInverse = $this->field->inverse($errorLocations[$i]);
			$denominator = 1;
			for ($j = 0; $j < $s; $j++) {
				if ($i != $j) {
					//denominator = field.multiply(denominator,
					//    GenericGF.addOrSubtract(1, field.multiply(errorLocations[j], xiInverse)));
					// Above should work but fails on some Apple and Linux JDKs due to a Hotspot bug.
					// Below is a funny-looking workaround from Steven Parkes
					$term = $this->field->multiply($errorLocations[$j], $xiInverse);
					$termPlus1 = ($term & 0x1) == 0 ? $term | 1 : $term & ~1;
					$denominator = $this->field->multiply($denominator, $termPlus1);
				}
			}
			$result[$i] = $this->field->multiply(
				$errorEvaluator->evaluateAt($xiInverse),
				$this->field->inverse($denominator)
			);
			if ($this->field->getGeneratorBase() != 0) {
				$result[$i] = $this->field->multiply($result[$i], $xiInverse);
			}
		}

		return $result;
	}
}
