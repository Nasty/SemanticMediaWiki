<?php

namespace SMW\Tests\SPARQLStore\QueryEngine;

use SMW\Tests\Util\StringBuilder;

use SMW\SPARQLStore\QueryEngine\QueryConditionBuilder;
use SMW\DIProperty;
use SMW\DIWikiPage;

use SMWDINumber as DINumber;
use SMWDIBlob as DIBlob;
use SMWDITime as DITime;
use SMW\Query\Language\ValueDescription as ValueDescription;
use SMW\Query\Language\SomeProperty as SomeProperty;
use SMWPrintRequest as PrintRequest;
use SMWPropertyValue as PropertyValue;
use SMW\Query\Language\ThingDescription as ThingDescription;
use SMW\Query\Language\Conjunction as Conjunction;
use SMW\Query\Language\Disjunction as Disjunction;
use SMW\Query\Language\ClassDescription as ClassDescription;
use SMW\Query\Language\NamespaceDescription as NamespaceDescription;

/**
 * @covers \SMW\SPARQLStore\QueryEngine\QueryConditionBuilder
 *
 * @ingroup Test
 *
 * @group SMW
 * @group SMWExtension
 * @group semantic-mediawiki-sparql
 * @group semantic-mediawiki-query
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class QueryConditionBuilderTest extends \PHPUnit_Framework_TestCase {

	private $stringBuilder;

	protected function setUp() {
		parent::setUp();

		$this->stringBuilder = new StringBuilder();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\QueryConditionBuilder',
			new QueryConditionBuilder()
		);
	}

	public function testQueryForSingleProperty() {

		$property = new DIProperty( 'Foo' );

		$description = new SomeProperty(
			$property,
			new ThingDescription()
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Foo ?v1 .'  )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForSinglePropertyWithValue() {

		$description = new ValueDescription(
			new DIBlob( 'SomePropertyValue' ),
			new DIProperty( 'Foo' )
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\SingletonCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '"SomePropertyValue" swivt:page ?url .' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForSomePropertyWithValue() {

		$property = new DIProperty( 'Foo' );

		$description = new SomeProperty(
			$property,
			new ValueDescription( new DIBlob( 'SomePropertyBlobValue' ) )
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Foo "SomePropertyBlobValue" .'  )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForSinglePageTypePropertyWithValueComparator() {

		$property = new DIProperty( 'Foo' );
		$property->setPropertyTypeId( '_wpg' );

		$description = new SomeProperty(
			$property,
			new ValueDescription( new DIWikiPage( 'SomePropertyPageValue', NS_MAIN ), null, SMW_CMP_LEQ )
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Foo ?v1 .' )->addNewLine()
			->addString( 'FILTER( ?v1sk <= "SomePropertyPageValue" )' )->addNewLine()
			->addString( '?v1 swivt:wikiPageSortKey ?v1sk .' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForSingleBlobTypePropertyWithNotLikeComparator() {

		$property = new DIProperty( 'Foo' );
		$property->setPropertyTypeId( '_txt' );

		$description = new SomeProperty(
			$property,
			new ValueDescription( new DIBlob( 'SomePropertyBlobValue' ), null, SMW_CMP_NLKE )
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Foo ?v1 .' )->addNewLine()
			->addString( 'FILTER( !regex( ?v1, "^SomePropertyBlobValue$", "s") )' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForSingleCategory() {

		$description = new ClassDescription(
			new DIWikiPage( 'Foo', NS_CATEGORY, '' )
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( "{ ?result rdf:type wiki:Category-3AFoo . }" )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForSingleNamespace() {

		$description = new NamespaceDescription( NS_HELP );

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$this->assertSame( 12, NS_HELP );

		$expectedConditionString = $this->stringBuilder
			->addString( '{ ?result swivt:wikiNamespace "12"^^xsd:integer . }' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForPropertyConjunction() {

		$conjunction = new Conjunction( array(
			new SomeProperty(
				new DIProperty( 'Foo' ), new ValueDescription( new DIBlob( 'SomePropertyValue' ) ) ),
			new SomeProperty(
				new DIProperty( 'Bar' ), new ThingDescription() ),
		) );

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $conjunction );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Foo "SomePropertyValue" .' )->addNewLine()
			->addString( '?result property:Bar ?v2 .' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForPropertyConjunctionWithGreaterLessEqualFilter() {

		$conjunction = new Conjunction( array(
			new SomeProperty(
				new DIProperty( 'Foo' ),
				new ValueDescription( new DINumber( 1 ), null, SMW_CMP_GEQ ) ),
			new SomeProperty(
				new DIProperty( 'Bar' ),
				new ValueDescription( new DINumber( 9 ), null, SMW_CMP_LEQ ) ),
		) );

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $conjunction );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Foo ?v1 .' )->addNewLine()
			->addString( 'FILTER( ?v1 >= "1"^^xsd:double )' )->addNewLine()
			->addString( '?result property:Bar ?v2 .' )->addNewLine()
			->addString( 'FILTER( ?v2 <= "9"^^xsd:double )' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForPropertyDisjunction() {

		$conjunction = new Disjunction( array(
			new SomeProperty( new DIProperty( 'Foo' ), new ThingDescription() ),
			new SomeProperty( new DIProperty( 'Bar' ), new ThingDescription() )
		) );

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $conjunction );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '{' )->addNewLine()
			->addString( '?result property:Foo ?v1 .' )->addNewLine()
			->addString( '} UNION {' )->addNewLine()
			->addString( '?result property:Bar ?v2 .' )->addNewLine()
			->addString( '}' )
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testQueryForPropertyDisjunctionWithLikeNotLikeFilter() {

		$conjunction = new Disjunction( array(
			new SomeProperty(
				new DIProperty( 'Foo' ),
				new ValueDescription( new DIBlob( "AA*" ), null, SMW_CMP_LIKE ) ),
			new SomeProperty(
				new DIProperty( 'Bar' ),
				new ValueDescription( new DIBlob( "BB?" ), null, SMW_CMP_NLKE )  )
		) );

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $conjunction );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '{' )->addNewLine()
			->addString( '?result property:Foo ?v1 .' )->addNewLine()
			->addString( 'FILTER( regex( ?v1, "^AA.*$", "s") )' )->addNewLine()
			->addString( '} UNION {' )->addNewLine()
			->addString( '?result property:Bar ?v2 .' )->addNewLine()
			->addString( 'FILTER( !regex( ?v2, "^BB.$", "s") )' )->addNewLine()
			->addString( '}' )
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testSingleDatePropertyWithGreaterEqualConstraint() {

		$property = new DIProperty( 'SomeDateProperty' );
		$property->setPropertyTypeId( '_dat' );

		$description = new SomeProperty(
			$property,
			new ValueDescription( new DITime( 1, 1970, 01, 01, 1, 1 ), null, SMW_CMP_GEQ )
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:SomeDateProperty-23aux ?v1 .' )->addNewLine()
			->addString( 'FILTER( ?v1 >= "2440587.5423611"^^xsd:double )' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	public function testSingleSubobjectBuildAsAuxiliaryProperty() {

		$property = new DIProperty( '_SOBJ' );

		$description = new SomeProperty(
			$property,
			new ThingDescription()
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Has_subobject-23aux ?v1 .' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	/**
	 * '[[HasSomeProperty::Foo||Bar]]'
	 */
	public function testSubqueryDisjunction() {

		$property = new DIProperty( 'HasSomeProperty' );
		$property->setPropertyTypeId( '_wpg' );

		$disjunction = new Disjunction( array(
			new ValueDescription( new DIWikiPage( 'Foo', NS_MAIN ), $property ),
			new ValueDescription( new DIWikiPage( 'Bar', NS_MAIN ), $property )
		) );

		$description = new SomeProperty(
			$property,
			$disjunction
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:HasSomeProperty ?v1 .' )->addNewLine()
			->addString( 'FILTER( ?v1 = wiki:Foo || ?v1 = wiki:Bar )' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	/**
	 * '[[Born in::<q>[[Category:City]] [[Located in::Outback]]</q>]]'
	 */
	public function testNestedPropertyConjunction() {

		$property = DIProperty::newFromUserLabel( 'Born in' );
		$property->setPropertyTypeId( '_wpg' );

		$conjunction = new Conjunction( array(
			new ClassDescription( new DIWikiPage( 'City', NS_CATEGORY ) ),
			new SomeProperty(
				DIProperty::newFromUserLabel( 'Located in' ),
				new ValueDescription(
					new DIWikiPage( 'Outback', NS_MAIN ),
					DIProperty::newFromUserLabel( 'Located in' ) )
				)
			)
		);

		$description = new SomeProperty(
			$property,
			$conjunction
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:Born_in ?v1 .' )->addNewLine()
			->addString( '{ ' )
			->addString( '{ ?v1 rdf:type wiki:Category-3ACity . }' )->addNewLine()
			->addString( '?v1 property:Located_in wiki:Outback .' )->addNewLine()
			->addString( '}' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

	/**
	 * '[[LocatedIn.MemberOf::Wonderland]]'
	 */
	public function testPropertyChain() {

		$description = new SomeProperty(
			DIProperty::newFromUserLabel( 'LocatedIn' ),
			new SomeProperty(
				DIProperty::newFromUserLabel( 'MemberOf' ),
				new ValueDescription(
					new DIWikiPage( 'Wonderland', NS_MAIN, '' ),
					DIProperty::newFromUserLabel( 'MemberOf' ), SMW_CMP_EQ
				)
			)
		);

		$instance = new QueryConditionBuilder();

		$condition = $instance->buildCondition( $description );

		$this->assertInstanceOf(
			'\SMW\SPARQLStore\QueryEngine\Condition\WhereCondition',
			$condition
		);

		$expectedConditionString = $this->stringBuilder
			->addString( '?result property:LocatedIn ?v1 .' )->addNewLine()
			->addString( '{ ?v1 property:MemberOf wiki:Wonderland .' )->addNewLine()
			->addString( '}' )->addNewLine()
			->getString();

		$this->assertEquals(
			$expectedConditionString,
			$instance->convertConditionToString( $condition )
		);
	}

}