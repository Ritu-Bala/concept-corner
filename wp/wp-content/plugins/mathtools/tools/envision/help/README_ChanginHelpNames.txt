June 28, 2013
---

Each tool has two HTML files associated with it. For example:

EnvisionPanBalanceHelp.html  -- The help file launched in the English tool. 
EnvisionPanBalanceHelp_es_US.html -- The help file launch in the Spanish tool.

These file names SHOULD NOT change. These are specified in the build for the tool. So if you ever change these, then the tool would have to be edited and rebuilt.

====

Each tool has two PDF files associated with it. For example:

PanBalance_StepbyStep.pdf  -- the English HTML file above redirects to this English PDF.
PanBalance_StepbyStep_es_US.pdf -- the Spanish HTML file above redirects to this Spanish PDF.

If you decide to change the name of any of these PDFs, you will need to do the following: 

1. Find the associated HTML file (refer to example above) and open it in a text editor. 
2. You will see a line with the following text:

	<meta http-equiv="Refresh" content="0; url=PanBalance_StepbyStep.pdf"/>

Leaving everything else the same, just edit the url value. For example, if the new PDF name is "newname.pdf" then you would edit that line to be:

	<meta http-equiv="Refresh" content="0; url=newname.pdf"/>

3. Save the file. To test if it worked, open the HTML file in a browser and it should automatically redirect to the new PDF.


====
