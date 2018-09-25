/* var ssoDistricts = [
	{ label : 'Albuquerque Public Schools', value: 'abcdef' },
	{ label : 'Aldine Independent School District', value: 'abcdef' },
	{ label : 'Alpine School District', value: 'abcdef' },
	{ label : 'Anchorage School District', value: 'abcdef' },
	{ label : 'Anne Arundel County Public Schools', value: 'abcdef' },
	{ label : 'Arlington Independent School District', value: 'abcdef' },
	{ label : 'Atlanta Public Schools', value: 'abcdef' },
	{ label : 'Austin Independent School District', value: 'abcdef' },
	{ label : 'Baltimore City Public School System', value: 'abcdef' },
	{ label : 'Baltimore County Public Schools', value: 'abcdef' },
	{ label : 'Boston Public Schools', value: 'abcdef' },
	{ label : 'Brevard Public Schools', value: 'abcdef' },
	{ label : 'Broward County Public Schools', value: 'abcdef' },
	{ label : 'Brownsville Independent School District', value: 'abcdef' },
	{ label : 'Capistrano Unified School District', value: 'abcdef' },
	{ label : 'Charlotte-Mecklenburg Schools', value: 'abcdef' },
	{ label : 'Cherry Creek School District', value: 'abcdef' },
	{ label : 'Chesterfield County Public Schools', value: 'abcdef' },
	{ label : 'Chicago Public Schools', value: 'abcdef' },
	{ label : 'Clark County School District', value: 'abcdef' },
	{ label : 'Clayton County Public Schools', value: 'abcdef' },
	{ label : 'Cobb County School District', value: 'abcdef' },
	{ label : 'Columbus City Schools', value: 'abcdef' },
	{ label : 'Conroe Independent School District', value: 'abcdef' },
	{ label : 'Corona-Norco Unified School District', value: 'abcdef' },
	{ label : 'Cumberland County Schools', value: 'abcdef' },
	{ label : 'Cypress-Fairbanks Independent School District', value: 'abcdef' },
	{ label : 'Dallas Independent School District', value: 'abcdef' },
	{ label : 'Davis School District', value: 'abcdef' },
	{ label : 'DeKalb County School System', value: 'abcdef' },
	{ label : 'Denver Public Schools', value: 'abcdef' },
	{ label : 'Detroit Public Schools', value: 'abcdef' },
	{ label : 'Douglas County School District RE-1', value: 'abcdef' },
	{ label : 'Duval County Public Schools', value: 'abcdef' },
	{ label : 'El Paso Independent School District', value: 'abcdef' },
	{ label : 'Elk Grove Unified School District', value: 'abcdef' },
	{ label : 'Fairfax County Public Schools', value: 'abcdef' },
	{ label : 'Fairfax County Public Schools', value: 'abcdef' },
	{ label : 'Fort Bend Independent School District', value: 'abcdef' },
	{ label : 'Fort Worth Independent School District', value: 'abcdef' },
	{ label : 'Fresno Unified School District', value: 'abcdef' },
	{ label : 'Fulton County School System', value: 'abcdef' },
	{ label : 'Garden Grove Unified School District', value: 'abcdef' },
	{ label : 'Garland Independent School District', value: 'abcdef' },
	{ label : 'Granite School District', value: 'abcdef' },
	{ label : 'Greenville County School District', value: 'abcdef' },
	{ label : 'Guilford County Schools', value: 'abcdef' },
	{ label : 'Gwinnett County Public Schools', value: 'abcdef' },
	{ label : 'Henrico County Public Schools', value: 'abcdef' },
	{ label : 'Hillsborough County Public Schools', value: 'abcdef' },
	{ label : 'Houston Independent School District', value: 'abcdef' },
	{ label : 'Howard County Public Schools', value: 'abcdef' },
	{ label : 'Jefferson County Public Schools', value: 'abcdef' },
	{ label : 'Jefferson County Public Schools', value: 'abcdef' },
	{ label : 'Jordan School District', value: 'abcdef' },
	{ label : 'Katy Independent School District', value: 'abcdef' },
	{ label : 'Knox County Schools', value: 'abcdef' },
	{ label : 'Lewisville Independent School District', value: 'abcdef' },
	{ label : 'Long Beach Unified School District', value: 'abcdef' },
	{ label : 'Los Angeles Unified School District', value: 'abcdef' },
	{ label : 'Loudoun County Public Schools', value: 'abcdef' },
	{ label : 'Memphis City Schools', value: 'abcdef' },
	{ label : 'Mesa Public Schools', value: 'abcdef' },
	{ label : 'Metropolitan Nashville Public Schools (Davidson County)', value: 'abcdef' },
	{ label : 'Miami-Dade County Public Schools', value: 'abcdef' },
	{ label : 'Milwaukee Public Schools', value: 'abcdef' },
	{ label : 'Mobile County Public School System', value: 'abcdef' },
	{ label : 'Montgomery County Public Schools', value: 'abcdef' },
	{ label : 'New York City Department of Education', value: 'abcdef' },
	{ label : 'North East Independent School District', value: 'abcdef' },
	{ label : 'Northside Independent School District', value: 'abcdef' },
	{ label : 'Omaha Public Schools', value: 'abcdef' },
	{ label : 'Orange County Public Schools', value: 'abcdef' },
	{ label : 'Pasadena Independent School District', value: 'abcdef' },
	{ label : 'Pasco County Schools', value: 'abcdef' },
	{ label : 'Pinellas County Schools', value: 'abcdef' },
	{ label : 'Plano Independent School District', value: 'abcdef' },
	{ label : 'Polk County Public Schools', value: 'abcdef' },
	{ label : 'Prince George\'s County Public Schools', value: 'abcdef' },
	{ label : 'Prince William County Public Schools', value: 'abcdef' },
	{ label : 'Puerto Rico School District', value: 'abcdef' },
	{ label : 'Sacramento City Unified School District', value: 'abcdef' },
	{ label : 'San Antonio Independent School District', value: 'abcdef' },
	{ label : 'San Bernardino City Unified School District', value: 'abcdef' },
	{ label : 'San Diego Unified School District', value: 'abcdef' },
	{ label : 'San Francisco Unified School District', value: 'abcdef' },
	{ label : 'Santa Ana Unified School District', value: 'abcdef' },
	{ label : 'School District of Lee County', value: 'abcdef' },
	{ label : 'School District of Osceola County, Florida', value: 'abcdef' },
	{ label : 'School District of Palm Beach County', value: 'abcdef' },
	{ label : 'School District of Philadelphia', value: 'abcdef' },
	{ label : 'Seattle Public Schools', value: 'abcdef' },
	{ label : 'Seminole County Public Schools', value: 'abcdef' },
	{ label : 'Shelby County Schools', value: 'abcdef' },
	{ label : 'Tucson Unified School District', value: 'abcdef' },
	{ label : 'Virginia Beach City Public Schools', value: 'abcdef' },
	{ label : 'Volusia County Schools', value: 'abcdef' },
	{ label : 'Wake County Public School System', value: 'abcdef' },
	{ label : 'Washoe County School District', value: 'abcdef' },
	{ label : 'Wichita Public Schools', value: 'abcdef' },
	{ label : 'Winston-Salem/Forsyth County Schools', value: 'abcdef' },
]; */

// check to see if there any additional districts
var ssoDistricts = [];


if ( typeof pssoConfDist !== 'undefined' ) {

	var arrResult    = [],
		nonDupeArray = [],
		startArray   = [],
		addArray     = pssoConfDist,
		i,
		n;

	startArray.push.apply( startArray, addArray );

	for ( i = 0, n = startArray.length; i < n; i++ ) {
		var item = startArray[ i ];
		arrResult[ item.label ] = item;
	}
	i = 0;
	for( item in arrResult ) {
		nonDupeArray[i++] = arrResult[ item ];
	}

	ssoDistricts = nonDupeArray;
}
