// gzgpst.cpp: implementation of the gzgpst class.
// code.google.com/p/dorothy-project
//////////////////////////////////////////////////////////////////////

#include "gzgpst.h"
# include "math.h"
# include "stdio.h"

/////////////////////////////////////////////////////////////////////
// Specification of Functions for jiami
// Precision: LSB=1/1024 sec

// Functions:
// unsigned int wgtochina_lb (int wg_flag, unsigned int wg_lng, unsigned int wg_lat, int wg_heit, int wg_week, unsigned int wg_time,  int *china_lng, unsigned int *china_lat)

// Input values:
// int wg_flag; /* initializing values flags (0 need initialization, 1 needn't) */
// unsigned int wg_lng; /* original longitude*/
// unsigned int wg_lat; /* original latitude*/
// int wg_heit; /*height, LBS=1m*/
// int wg_week; /*time weeks received from GPS*/
// unsigned int wg_time; /* GPS time(LBS=1/1000sec, by weeks)

// Output values: unsigned int *china_lng; /*decoded longitude*/
// Unsigned int china_lat; /* decoded latitude*/

// Return: 0x00000000 / OK//
// 0xFFFF95FF / error///



double casm_rr ;
unsigned int casm_t1;
unsigned int casm_t2;
double casm_x1;
double casm_y1;
double casm_x2;
double casm_y2;
double casm_f;
double yj_sin2(double x)
{
	double tt ;
	double ss;
        int ff ;
	double s2;
	int cc;
	ff=0;
	if (x<0)
	{
		x=-x;
		ff=1;
	}
	cc=x/6.28318530717959;
	tt=x-cc*6.28318530717959;
	if (tt>3.1415926535897932)
	{
	    tt=tt-3.1415926535897932;
            if (ff==1)
               ff=0;
	    else if (ff==0)
               ff=1;
	}
	x=tt;
	ss=x;
	s2=x;
	tt=tt*tt;
	s2=s2*tt;
	ss=ss-s2* 0.166666666666667;
	s2=s2*tt;
	ss=ss+s2* 8.33333333333333E-03;
	s2=s2*tt;
	ss=ss-s2* 1.98412698412698E-04;
	s2=s2*tt;
	ss=ss+s2* 2.75573192239859E-06;
	s2=s2*tt;
	ss=ss-s2* 2.50521083854417E-08;
	if (ff==1)
		ss=-ss;
	return ss;
}
double Transform_yj5(double x , double y )
{
	double tt;
	tt = 300 + 1 * x + 2 * y + 0.1 * x * x + 0.1 * x * y + 0.1 * sqrt(sqrt(x*x));
	tt = tt + (20 *yj_sin2(18.849555921538764 * x ) + 20 * yj_sin2(6.283185307179588 * x))*0.6667;
	tt = tt + (20 * yj_sin2(3.141592653589794 * x ) + 40 * yj_sin2(1.047197551196598 * x))*0.6667;
	tt = tt + (150 * yj_sin2(0.2617993877991495 * x) + 300 * yj_sin2(0.1047197551196598 * x))*0.6667;
	return tt;
}
double Transform_yjy5(double x , double y )
{
	double tt ;
	tt = -100 +  2 * x + 3 * y + 0.2 * y * y + 0.1 * x * y + 0.2 * sqrt(sqrt(x*x));
	tt = tt + (20 * yj_sin2(18.849555921538764 * x) + 20 * yj_sin2(6.283185307179588 * x))*0.6667;
	tt = tt + (20 * yj_sin2(3.141592653589794 * y)+ 40 * yj_sin2(1.047197551196598 * y))*0.6667;
	tt = tt + (160 * yj_sin2(0.2617993877991495 * y) + 320 * yj_sin2(0.1047197551196598 * y))*0.6667;
	return tt;
}
double Transform_jy5(double x , double xx )
{
	double n ;
	double a ;
	double e ;
	a = 6378245;
	e = 0.00669342;
	n = sqrt (1 - e * yj_sin2(x * 0.0174532925199433) * yj_sin2(x * 0.0174532925199433) );
	n = (xx * 180) /(a / n * cos(x * 0.0174532925199433) * 3.1415926) ;
	return n;
}
double Transform_jyj5(double x , double yy )
{
	double m ;
	double a ;
	double e ;
        double mm;
	a = 6378245;
	e = 0.00669342;
	 mm= 1 - e * yj_sin2(x * 0.0174532925199433) * yj_sin2(x * 0.0174532925199433) ;
	m = (a * (1 - e)) / (mm * sqrt(mm));
	return (yy * 180) / (m * 3.1415926);
}
double r_yj()
{
       int casm_a ;
       int casm_c ;
	casm_a = 314159269;
	casm_c = 453806245;
	return 0;
}
double random_yj()
{
       int t;
       int casm_a ;
       int casm_c ;
	casm_a = 314159269;
	casm_c = 453806245;
	casm_rr = casm_a * casm_rr + casm_c ;
	t = casm_rr /2 ;
	casm_rr = casm_rr - t * 2;
	casm_rr = casm_rr / 2 ;
	return (casm_rr);
}
void IniCasm(unsigned int w_time, unsigned int w_lng, unsigned int w_lat)
{
	int tt;
	casm_t1 = w_time ;
	casm_t2 = w_time ;
	tt=w_time/0.357;
	casm_rr=w_time-tt*0.357;
	if (w_time==0)
           casm_rr=0.3;
	casm_x1 = w_lng;
	casm_y1 = w_lat;
	casm_x2 = w_lng;
	casm_y2 = w_lat;
	casm_f=3;
}
unsigned int wgtochina_lb(int wg_flag, unsigned int wg_lng, unsigned int wg_lat, int wg_heit,  int wg_week, unsigned int wg_time, unsigned  int *china_lng, unsigned int *china_lat)
{
	double x_add ;
	double y_add ;
	double h_add ;
	double x_l;
	double y_l;
	double casm_v ;
        double t1_t2;
        double x1_x2;
        double y1_y2;

	

        if (wg_heit>5000)
	{
		*china_lng = 0 ;
		*china_lat = 0;
		return 0xFFFF95FF;
	}
	x_l =  wg_lng;
	x_l =  x_l / 3686400.0;
	y_l =  wg_lat ;
	y_l =  y_l / 3686400.0;
      if (x_l < 72.004)
	{
		*china_lng = 0 ;
		*china_lat = 0;
		return 0xFFFF95FF;
	}
	if ( x_l > 137.8347)
	{
		*china_lng = 0 ;
		*china_lat = 0;
		return 0xFFFF95FF;
	}
	if (y_l < 0.8293)
	{
		*china_lng = 0 ;
		*china_lat = 0;
		return 0xFFFF95FF;
	}
	if ( y_l > 55.8271)
	{
		*china_lng = 0 ;
		*china_lat = 0;
		return 0xFFFF95FF;
	}
	if (wg_flag ==0)
	{
	    IniCasm(wg_time,wg_lng,wg_lat);
	    *china_lng = wg_lng;
	    *china_lat = wg_lat;
	    return 0x00000000;
	}

	casm_t2= wg_time ;
	t1_t2 =(double)(casm_t2 - casm_t1)/1000.0;
	if ( t1_t2<=0 )
	{
		casm_t1= casm_t2 ;
                casm_f=casm_f+1;
		casm_x1 = casm_x2;
                casm_f=casm_f+1;
		casm_y1 = casm_y2;
                casm_f=casm_f+1;
	}
	else
	{
        if ( t1_t2 > 120 )
		{
			if (casm_f == 3)
                       {
			casm_f=0;
			casm_x2 = wg_lng;
			casm_y2 = wg_lat;
                        x1_x2 = casm_x2 - casm_x1;
                        y1_y2 = casm_y2 - casm_y1;
			casm_v = sqrt (x1_x2 * x1_x2 + y1_y2 * y1_y2 ) /t1_t2;
			if (casm_v  > 3185)
			{
				*china_lng = 0 ;
	            		*china_lat = 0;
				return (0xFFFF95FF);
			}

                       }
			casm_t1= casm_t2 ;
                        casm_f=casm_f+1;
			casm_x1 = casm_x2;
                        casm_f=casm_f+1;
			casm_y1 = casm_y2;
                        casm_f=casm_f+1;
		}
	}
	x_add = Transform_yj5(x_l - 105, y_l - 35) ;
	y_add = Transform_yjy5(x_l - 105, y_l - 35) ;
	h_add = wg_heit;

	x_add = x_add + h_add * 0.001 + yj_sin2(wg_time*0.0174532925199433)+random_yj();
	y_add = y_add + h_add * 0.001 + yj_sin2(wg_time*0.0174532925199433)+random_yj();
	*china_lng = (x_l + Transform_jy5(y_l, x_add)) * 3686400;
	*china_lat = (y_l + Transform_jyj5(y_l, y_add)) * 3686400;
	return (0x00000000);
}


