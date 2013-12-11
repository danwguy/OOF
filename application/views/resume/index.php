
<script type='text/javascript'>
    resume.currentPage = 1;
    $(document).ready(function () {
        resume.showPage(resume.currentPage);
        $('skills-inner').each(function () {
            $(this).css({
                'height': '1px'
            })
        });
        $('.content').css({
            'left' : '28.5px'
        });
        page.setPageContent('resume', $('.content').html());
        $('.bottom-paginate').find('div').each(function() {
            $(this).click(function() {
                if($(this).hasClass('disabled') || $(this).hasClass('page-id')) {
                    return false;
                }
                var clickedPage = $(this).attr('page');
                resume.currentPage = clickedPage;
                resume.showPage(clickedPage);
            })
        });
        $('.php-fill').css({
            'height': '100%'
        });
        $('.js-fill').css({
            'height' : '100%'
        });
        $('.css-fill').css({
            'height' : '90%'
        });
        $('.mysql-fill').css({
            'height' : '85%'
        });
        $('.sql-fill').css({
            'height' : '95%'
        });
        $('.java-fill').css({
            'height' : '50%'
        });
        $('.regex-fill').css({
            'height' : '70%'
        })
    })
</script>
<div class='resume-header'>
    <div class='skills-outter'>
        <div class='skills-inner php-fill' fill='100'></div>
    </div>
    <div class='skills-title php-title'>PHP</div>
    <div class='skills-outter'>
        <div class='skills-inner js-fill' fill='100'></div>
    </div>
    <div class='skills-title js-title'>JavaScript</div>
    <div class='skills-outter'>
        <div class='skills-inner css-fill' fill='90'></div>
    </div>
    <div class='skills-title css-title'>CSS3</div>
    <div class='skills-outter'>
        <div class='skills-inner mysql-fill' fill='85'></div>
    </div>
    <div class='skills-title mysql-title'>MySQL</div>
    <div class='skills-outter'>
        <div class='skills-inner sql-fill' fill='95'></div>
    </div>
    <div class='skills-title sql-title'>SQL Server</div>
    <div class='skills-outter'>
        <div class='skills-inner java-fill' fill='50'></div>
    </div>
    <div class='skills-title java-title'>Java</div>
    <div class='skills-outter'>
        <div class='skills-inner regex-fill' fill='70'></div>
    </div>
    <div class='skills-title regex-title'>Regex</div>
</div>

    <div class='resume-data'>
        <h1 class='name'>Robert Mason</h1>

        <h3 class='position'>Web Developer</h3>
        <div class='pagination' page='1'>

        <h3 class='summary'>Summary</h3>

        <p class='summary-description'>
            Knowledgeable Web Developer with over 8 years of experience programming<br />
            web and standalone applications using the latest technologies to provide<br />
            rich, responsive applications. Advanced proficiency with OOP<br />
            programming using MVC architecture.
        </p>

        <h3 class='experience'>Experience</h3>
            <p class='company-info'>
                <span class='company-title'>PHP DEVELOPER / PROJECT LEAD -- Linktech Worldwide</span>
                <br />
                <span class='employment-dates'>June 2011 - June 2013</span>
            </p>

        <p class='job-description paragraph'>
            Acting as both senior programmer and project manager, I lead a team of developers in the development of
            a custom CRM solution as a replacement for Microsoft Dynamics CRM. The application encompasses every
            aspect of the company’s business logic for all departments by managing the 20k+ customer base. It was
            written in PHP using OO practices and an MVC pattern with a MySQL database. Included with this application,
            we built a fully automated billing system that allows the company full control over charging and tracking
            customers by switching them from ARB customers to CIM customers. In order to ensure the application was
            launched successfully and on time I implemented a modified SCRUM development process that ensured a
            very rigorous QA process. All of the bug tracking and ticketing was done using a custom submission system
            using the Jira API to allow users to submit tickets and bugs directly to the development staff. The program
            is
            currently supporting 200+ users, performing 8,000+ tasks daily, and handles the maintenance and billing
            of the company’s 20k+ customer base. Results included the standardization of workflows throughout the
            company, a vast reduction of operating costs through automation of menial tasks, and production of a wider
            array of reports to management allowing for the implementation of more profitable business practices.
        </p>

        <p class='job-description paragraph'>

            Also while here I acted as senior Programmer and Project Manager for the development of a sales interface,
            which was later expanded to the Customer Service, Retention, Up and Running, and Web Optimization
            departments. This application is responsible for handling the 100+ sales agents on sales floors in
            Beaverton, OR and in Beverly Hills, CA. Since it’s implementation it has doubled the company’s client base
            signing up on average 100+ new clients daily. To achieve this it was built and designed with automation and
            speed in mind. It is a single page application that never requires reloading or manually hitting a ‘save’
            button.
            In order to ensure the application was able to handle 50k+ phone calls a day I designed my own method of
            JavaScript programming called ZGC, or Zero Garbage Collection, programming. It allows the application to
            handle an absurd amount of data without ever bogging down regardless how long the page is up for. Included
            with it we also integrated Google Maps allowing the sales agent to quickly find additional products for the
            customer in real-time by using geo-spacial querying against the 2005 SQL Server database, and a custom
            built promotions system which allows management to customize the price points on the fly for each campaign.
            The results of this application included bringing the company up to PCI compliance by implementing a custom
            AES encryption algorithm in both JavaScript and PHP which uses counter mode to encrypt all customer
            credit card information, doubling the customer base by automating the sales process, and allowing the
            company
            to quickly and easily launch new sales campaigns in any market they want increasing the possibility of new
            customers.
        </p>
    </div>

<div class='pagination' page='2'>
    <p class='company-info'>
        <span class='company-title'>IT HELP DESK -- Knox Attorney Services</span>
        <br />
        <span class='employment-dates'>October 2010 - April 2011</span>
    </p>

        <p class='job-description paragraph'>
            I was the top tier IT guy here and handled all requests that were unable to be complete by other members
            of the IT department. I was responsible for resolving all IT issues for the offices in San Diego, Santa Ana,
            Los Angeles, Las Vegas, and San Fransisco. I handled all network related issues for all offices and was tasked
            with ensuring that all offices had 100% up-time during critical business hours. I installed and upgraded
            multiple servers at all locations. During my employment  was able increase the productivity of the printing
            department by 40% by writing an application that was able to calculate the  estimated printing time of a job
            that was being sent in,, weigh it against the priority level assigned to it by management and the other jobs
            that were in the queue vs how many printers were actively online and available to handle a print job, and then
            re-write the print queue based on those factors. I also introduced the company to the google AdSense API
            and helped them develop a marketing plan to increase sales with minimal impact on the budget.
        </p>

    <p class='company-info'>
        <span class='company-title'>Self-Employed Contractor -- Freelance Programming</span>
        <br />
        <span class='employment-dates'>July 2009 - current</span>
    </p>
        <p class='job-description paragraph'>

            Provided technical support for individuals and small businesses. Designed and developed an online college
            that allowed students to take courses while video conferencing with their instructors. The video conferencing
            was built using ActionScript, it did not require special servers since it used load balancing tests to find the
            person with the highest bandwidth available and used their machine as the “server” for the conference. Designed
            and built a prototype application for a company that created an environment on a single page that would
            replace a management studio for a television broadcasting company. Using JavaScript and PHP I programmed
            this application that allowed multiple video streams to play simultaneously while giving the user the ability
            to re-order, remove, add, and change the priority levels of each video. As one video gained priority and
            screen real estate, the other videos would scale down accordingly. I helped companies implement coding
            standards and QA policies to ensure timely releases of bug free applications with uniformity amongst all
            devs, contractor and salaried alike. Also developed a translation engine for a Russian company that was able
            to translate different dialects of russian to english and was scalable enough to add any language with ease.
            Built a notification system using Node.js and Socket.io that was able to accept incoming calls and pass them
            through to the correct department as a push message.
        </p>

    <p class='company-info'>
        <span class='company-title'>Field Engineer / IT Support - Adecco Engineering and Technical</span>
        <span class='employment-dates'>November 2007 - October 2008</span>
    </p>
    <p class='job-description paragraph'>

        Adecco is a staffing agency that I have done a number of assignments for, all temporary jobs. The first
        assignment I did for them was Field Service Engineer through a company called Cardinal Health. For this
        assignment I was flown to all the hospitals in the country to perform an FDA mandated remediation of
        the Alaris Patient Infusion Pumps. This was done by hooking the pump up to a laptop and upgrading the
        software that was installed on the pump with new software to fix a “key bounce” error that would cause the
        pumps to overdose medication. After upgrading the software I would set the pump up with a saline solution
        and re-calibrate the flow and pressure to ensure 100% accuracy of the medication delivered. The second
        assignment was for the same company, after all the pumps were upgraded and the first assignment was over,
        they asked me to stay on  as a technical support specialist. For this assignment I was the last line of support
        before the customer was escalated to “Customer Advocacy”. I would remotely troubleshoot all equipment in
        the hospitals we had contracts with. Most commonly, I would rebuild the SQL databases that housed all of the
        patient/medication information for that station. I would run backups of the databases, truncate them, rebuild
        them, then restore the backup data. I would also push out system wide updates using Aardvark.
    </p>
    </div>
</div>


<div class='bottom-paginate'>
    <div class='prev disabled' page='1'>Previous</div>
    <div class='page-id'>Page 1 of 2</div>
    <div class='next' page='2'>Next</div>
</div>