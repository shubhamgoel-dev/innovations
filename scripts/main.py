import csv
import requests
import sys
from bs4 import BeautifulSoup
import time

black_list_companies = ['GeekHunter', 'Pepsico', 'Walmart', 'Costco']

def make_request(url):
    response = requests.get(url)
    return response


def scrape_linkedin_jobs(webpage, max_pages=50):
    all_jobs = []
    unique_links = set()

    for page_number in range(max_pages):
        today = time.strftime("%Y-%m-%d")
        next_page = f"{webpage}&start={page_number}"
        response = make_request(next_page)
        if not response:
            continue

        soup = BeautifulSoup(response.content, 'html.parser')
        jobs = soup.find_all('div', class_='base-card relative w-full hover:no-underline focus:no-underline base-card--link base-search-card base-search-card--link job-search-card')

        for job in jobs:
            job_title = job.find('h3', class_='base-search-card__title').text.strip()
            job_company = job.find('h4', class_='base-search-card__subtitle').text.strip()
            job_location = job.find('span', class_='job-search-card__location').text.strip()
            job_link = job.find('a', class_='base-card__full-link')['href']
            job_date_element = job.find('time', class_='job-search-card__list-date')
            job_date = job_date_element.get('datetime') if job_date_element else today
            
            base_link = job_link.split('?')[0]

            if job_company not in black_list_companies and base_link not in unique_links:
                all_jobs.append([job_title, job_company, job_location, job_date, base_link])
                unique_links.add(base_link)

    return all_jobs

def save_jobs_to_csv(jobs, filename):
    with open(filename, 'w', newline='', encoding='utf-8-sig') as file:
        writer = csv.writer(file, delimiter=';')
        writer.writerow(['Title', 'Company', 'Location', 'Timestamp', 'Apply'])
        writer.writerows(jobs)

def main():
    position = sys.argv[1]
    location = sys.argv[2]
    level = sys.argv[3]
    time_posted = sys.argv[4]    
    work_type = sys.argv[5]
    
    # timestamp = time.strftime("%Y%m%d-%H%M%S")
    # filename = f'linkedin-jobs-{position}-{location}-{timestamp}.csv'
    
    webpage = f'https://www.linkedin.com/jobs-guest/jobs/api/seeMoreJobPostings/search?keywords={position}&location={location}&f_TPR={time_posted}&f_E={level}&f_WT={work_type}&trk=public_jobs_jobs-search-bar_search-submit&position=1&pageNum=0'
    print(webpage)
    # all_jobs = scrape_linkedin_jobs(webpage)
    # all_jobs.sort(key=lambda x: x[3], reverse=True)

    # print(all_jobs)
    # save_jobs_to_csv(all_jobs, filename)
    # print('File saved successfully.')

if __name__ == "__main__":
    main()
